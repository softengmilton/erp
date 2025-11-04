<?php

namespace App\Http\Controllers\Store\Report;

use App\Http\Controllers\Controller;
use App\Models\StoreProduct;
use App\Models\StoreProductType;
use App\Models\StoreStockItem;
use App\Models\StoreStockMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TotalAssetController extends Controller
{
    // public function index()
    // {
    //     // eager-load movements & related stock items to avoid N+1
    //     $products = StoreProduct::with([
    //         'storeStockMovements.storeStock',
    //         'storeStockMovements.storeStockItem',
    //     ])->get(['id', 'name']);

    //     // produce rows per product -> per purchase
    //     $rowsPerProduct = $products->map(function ($product) {
    //         $movements = $product->storeStockMovements;

    //         // sales for THIS product grouped by invoice_number
    //         $soldPerInvoice = $movements
    //             ->where('source_type', 'sale')
    //             ->groupBy(fn ($m) => $m->storeStock->invoice_number ?? 'N/A')
    //             ->map(fn ($group) => $group->sum('change_quantity')); // invoice_number => sold_qty

    //         // process purchase movements for THIS product
    //         $purchaseRows = $movements
    //             ->where('source_type', 'purchase')
    //             ->map(function ($purchase) use ($soldPerInvoice, $product) {
    //                 $invoiceNumber = $purchase->storeStock->invoice_number ?? 'N/A';
    //                 // sold qty for this invoice and this product (product scope preserved)
    //                 $soldQty = $soldPerInvoice->get($invoiceNumber, 0);

    //                 $sourceData = is_array($purchase->source_data)
    //                     ? $purchase->source_data
    //                     : json_decode($purchase->source_data, true);

    //                 $quantity = $purchase->change_quantity;
    //                 $unit_cost = $sourceData['unit_cost'] ?? 0;
    //                 $shipping = $sourceData['shipping'] ?? 0;
    //                 $fees = $sourceData['fees'] ?? 0;

    //                 $costing_per_product = $unit_cost + $shipping + $fees;
    //                 $sale_price = $purchase->storeStockItem->sale_price ?? 0;

    //                 $available_stock = $quantity - $soldQty;
    //                 if ($available_stock < 0) {
    //                     $available_stock = 0;
    //                 } // safety

    //                 $available_asset_buy_price = $available_stock * $costing_per_product;
    //                 $available_asset_sale_price = $available_stock * $sale_price;

    //                 return [
    //                     'product_id' => $product->id,
    //                     'product_name' => $product->name,
    //                     'invoice_number' => $invoiceNumber,
    //                     'purchase_quantity' => $quantity,
    //                     'sold_qty_on_invoice' => $soldQty,
    //                     'available_stock' => $available_stock,
    //                     'available_asset_buy_price' => $available_asset_buy_price,
    //                     'available_asset_sale_price' => $available_asset_sale_price,
    //                 ];
    //             });

    //         return $purchaseRows; // collection of this product's purchase rows
    //     });

    //     // flatten to single collection of rows (one row per purchase)
    //     $tableData = $rowsPerProduct->flatten(1);

    //     // grand totals
    //     $grands = [
    //         'grand_availble_asset_buy_price' => $tableData->sum('available_asset_buy_price'),
    //         'grand_availble_asset_sale_price' => $tableData->sum('available_asset_sale_price'),
    //     ];

    //     return Inertia::render('store/reports/Assets', [
    //         'grands' => $grands,
    //     ]);
    // }

    public function index(Request $request)
    {
        // 🟢 Get selected category (optional)
        $selectedCatId = $request->input('category_id');
        $productTypes = StoreProductType::select('id', 'name')->get();

        // 🟢 Get all or filtered products
        $productsQuery = StoreProduct::query()->orderBy('id', 'desc');

        if ($selectedCatId) {
            $productsQuery->where('store_product_type_id', $selectedCatId);
        }

        $products = $productsQuery->get();

        $allTableData = collect();
        $allGrands = [];

        foreach ($products as $product) {
            if (! $product) {
                continue;
            }

            // 🟢 Get all purchase movements
            $stockMovements = StoreStockMovement::with(['storeProduct', 'storeStock'])
                ->where('source_type', 'purchase')
                ->where('store_product_id', $product->id)
                ->get(['id', 'store_stock_id', 'store_product_id', 'change_quantity', 'source_data']);

            // 🟢 Get all sales grouped by invoice
            $soldPerInvoice = StoreStockMovement::with('storeStock')
                ->where('source_type', 'sale')
                ->where('store_product_id', $product->id)
                ->get()
                ->groupBy(fn ($item) => $item->storeStock->invoice_number ?? 'N/A')
                ->map(fn ($group) => $group->sum('change_quantity'));

            // 🟢 Get stock items grouped by invoice
            $stockItemsByInvoice = StoreStockItem::where('store_product_id', $product->id)
                ->with('storeStock')
                ->get()
                ->groupBy(fn ($item) => $item->storeStock->invoice_number ?? 'N/A');

            // 🧮 Calculate per-invoice metadata
            $invoiceMetaData = $stockItemsByInvoice->map(function ($items, $invoice) {
                $latest = $items->sortByDesc('id')->first();
                $meta = json_decode($latest->price_meta, true) ?? [];

                $priceMaxQty = [];

                $decodeMetaChain = function ($meta) use (&$decodeMetaChain, &$priceMaxQty) {
                    if (! is_array($meta)) {
                        $decoded = json_decode($meta, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $meta = $decoded;
                        } else {
                            return;
                        }
                    }

                    if (isset($meta['quantity_sold'], $meta['old_price'])) {
                        $price = (float) $meta['old_price'];
                        $qty = (float) $meta['quantity_sold'];
                        if (! isset($priceMaxQty[$price]) || $qty > $priceMaxQty[$price]) {
                            $priceMaxQty[$price] = $qty;
                        }
                    }

                    if (isset($meta['previous']) && $meta['previous']) {
                        $decodeMetaChain($meta['previous']);
                    }
                };

                $decodeMetaChain($meta);

                $dynamicSoldProductPrice = 0;
                foreach ($priceMaxQty as $price => $qty) {
                    $dynamicSoldProductPrice += $price * $qty;
                }

                $dynamicSalePrice = (float) ($meta['new_price'] ?? $latest->sale_price ?? 0);
                $totalStock = (float) ($latest->quantity ?? 0);
                $totalSoldQty = array_sum($priceMaxQty);
                $remainingQty = max(0, $totalStock - $totalSoldQty);
                $dynamicSaleAsset = $dynamicSoldProductPrice + ($remainingQty * $dynamicSalePrice);

                return [
                    'dynamicSalePrice' => $dynamicSalePrice,
                    'dynamicSaleAsset' => $dynamicSaleAsset,
                    'dynamicSoldProductPrice' => $dynamicSoldProductPrice,
                    'totalSoldQty' => $totalSoldQty,
                ];
            });

            // 🧾 Per-invoice table data
            $tableData = $stockMovements->map(function ($item) use ($soldPerInvoice, $invoiceMetaData) {
                $invoiceNumber = $item->storeStock->invoice_number ?? 'N/A';
                $soldQty = $soldPerInvoice[$invoiceNumber] ?? 0;
                $meta = $invoiceMetaData[$invoiceNumber] ?? [
                    'dynamicSalePrice' => 0,
                    'dynamicSaleAsset' => 0,
                    'dynamicSoldProductPrice' => 0,
                    'totalSoldQty' => 0,
                ];

                $dynamicSalePrice = $meta['dynamicSalePrice'];
                $dynamicSaleAsset = $meta['dynamicSaleAsset'];

                $sourceData = is_array($item->source_data)
                    ? $item->source_data
                    : json_decode($item->source_data, true);

                $quantity = $item->change_quantity;
                $unitCost = $sourceData['unit_cost'] ?? 0;
                $shipping = $sourceData['shipping'] ?? 0;
                $fees = $sourceData['fees'] ?? 0;
                $costingPerProduct = $unitCost + $shipping + $fees;

                $salePrice = $dynamicSalePrice;
                $soldProduct = $soldQty;

                $availableStock = $quantity - $soldQty;
                $availableAssetBuyPrice = $availableStock * $costingPerProduct;
                $availableAssetSalePrice = $availableStock * $salePrice;

                return [
                    'invoice_number' => $invoiceNumber,
                    'product_name' => $item->storeProduct->name ?? 'Unknown',
                    'quantity' => $quantity,
                    'costing_per_product' => $costingPerProduct,
                    'sale_price' => $salePrice,
                    'sold_product' => $soldProduct,
                    'availble_stock' => $availableStock,
                    'availble_asset_buy_price' => $availableAssetBuyPrice,
                    'availble_asset_sale_price' => $availableAssetSalePrice,
                ];
            });
            // ->filter(function ($row) {
            //     // 🧹 Remove rows whose available stock is zero
            //     return $row['availble_stock'] != 0;
            // });

            $tableData = $tableData->sortByDesc(fn ($row) => $row['invoice_number'])->values();

            // 🧮 Product-level grand totals
            $grands = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'grand_sold_product' => $tableData->sum('sold_product'),
                'grand_initial_stock' => $tableData->sum('quantity'),
                'grand_avaiable_stock' => $tableData->sum('availble_stock'),
                'grand_availble_asset_buy_price' => $tableData->sum('availble_asset_buy_price'),
                'grand_availble_asset_sale_price' => $tableData->sum('availble_asset_sale_price'),
            ];

            $allGrands[] = $grands;

            // 🧾 Add product rows and per-product grand total row
            $allTableData = $allTableData->concat($tableData);
            $allTableData->push([
                'invoice_number' => "Grand Total ({$product->name})",
                'product_name' => '-',
                'quantity' => $grands['grand_initial_stock'],
                'costing_per_product' => '-',
                'sale_price' => '-',
                'sold_product' => $grands['grand_sold_product'],
                'availble_stock' => $grands['grand_avaiable_stock'],
                'availble_asset_buy_price' => $grands['grand_availble_asset_buy_price'],
                'availble_asset_sale_price' => $grands['grand_availble_asset_sale_price'],
            ]);
        }

        // 🧮 Final grand total for all products
        if ($allGrands) {
            $finalGrand = [
                'invoice_number' => 'Grand Total (All Products)',
                'product_name' => '-',
                'quantity' => collect($allGrands)->sum('grand_initial_stock'),
                'costing_per_product' => '-',
                'sale_price' => '-',
                'sold_product' => collect($allGrands)->sum('grand_sold_product'),
                'availble_stock' => collect($allGrands)->sum('grand_avaiable_stock'),
                'availble_asset_buy_price' => collect($allGrands)->sum('grand_availble_asset_buy_price'),
                'availble_asset_sale_price' => collect($allGrands)->sum('grand_availble_asset_sale_price'),
            ];

            $allTableData->push($finalGrand);
        }
        // 🧹 Eliminate rows where available stock = 0
        // $allTableData = $allTableData->filter(function ($row) {
        //     return ! isset($row['availble_stock']) || $row['availble_stock'] != 0;
        // })->values();

        // 🪞 Return to Inertia
        return Inertia::render('store/reports/Assets', [
            'tableData' => $allTableData,
            'products' => $products,
            'productTypes' => $productTypes,
            'selectedCatId' => $selectedCatId,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        // dd($totalRemainingBuyAsset);
        // dd($totalRemainingSaleAsset);
    }

    /**
     * Store a newly created resource in storage.https://chatgpt.com/c/6907892a-3e9c-8322-afe1-31f98bb0340e
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
