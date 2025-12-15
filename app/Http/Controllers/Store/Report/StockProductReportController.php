<?php

namespace App\Http\Controllers\Store\Report;

use App\Http\Controllers\Controller;
use App\Models\StoreProduct;
use App\Models\StoreProductType;
use App\Models\StoreStockItem;
use App\Models\StoreStockMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockProductReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // Category based

    public function index(Request $request)
    {
        // Date
        $formattedDate = Carbon::now()->format('d M, Y');

        // Get category
        $selectedCatId = $request->input('category_id');
        $productTypes = StoreProductType::select('id', 'name')->get();
        $categoryId = $selectedCatId ?? StoreProductType::orderBy('id', 'desc')->value('id');

        // 🟢 Paginate products (Option 2)
        // $perPage = 3; // change to whatever number of products per page you want
        $products = StoreProduct::where('store_product_type_id', $categoryId)
            ->orderBy('id', 'desc')
            ->get();
        // ->paginate($perPage);

        $allTableData = collect();
        $allGrands = [];

        foreach ($products as $product) {

            // skip invalid product
            if (! $product) {
                continue;
            }

            // Get all purchase movements
            $stockMovements = StoreStockMovement::with(['storeProduct', 'storeStock'])
                ->where('source_type', 'purchase')
                ->where('store_product_id', $product->id)
                ->get(['id', 'store_stock_id', 'store_product_id', 'change_quantity', 'source_data']);

            // Get all sales grouped by invoice
            $soldPerInvoice = StoreStockMovement::with('storeStock')
                ->where('source_type', 'sale')
                ->where('store_product_id', $product->id)
                ->get()
                ->groupBy(fn($item) => $item->storeStock->invoice_number ?? 'N/A')
                ->map(fn($group) => $group->sum('change_quantity'));

            // All stock items for this product grouped by invoice
            $stockItemsByInvoice = StoreStockItem::where('store_product_id', $product->id)
                ->with('storeStock')
                ->get()
                ->groupBy(fn($item) => $item->storeStock->invoice_number ?? 'N/A');

            // Per-invoice meta computation
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

            // Build per-invoice table data
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
                $dynamicSoldProductPrice = $meta['dynamicSoldProductPrice'];
                $totalSoldQty = $meta['totalSoldQty'];

                $sourceData = is_array($item->source_data)
                    ? $item->source_data
                    : json_decode($item->source_data, true);

                $slodQtyUpPrice = $soldQty - $totalSoldQty;
                $quantity = $item->change_quantity;
                $unitCost = $sourceData['unit_cost'] ?? 0;
                $shipping = $sourceData['shipping'] ?? 0;
                $fees = $sourceData['fees'] ?? 0;
                $costingPerProduct = $unitCost + $shipping + $fees;

                $salePrice = $dynamicSalePrice;
                $salePriceAsset = $dynamicSaleAsset;
                $profitPerProduct = $salePrice - $costingPerProduct;
                $buyPriceAsset = $quantity * $costingPerProduct;
                $soldBuyProductPrice = $soldQty * $costingPerProduct;
                $soldProductPrice = $slodQtyUpPrice * $salePrice;
                $totalSoldProductPrice = $dynamicSoldProductPrice + $soldProductPrice;
                $totalProfitProduct = $totalSoldProductPrice - $soldBuyProductPrice;
                $availableStock = $quantity - $soldQty;
                $availableAssetBuyPrice = $availableStock * $costingPerProduct;
                $availableAssetSalePrice = $availableStock * $salePrice;

                return [
                    'invoice_number' => $invoiceNumber,
                    'product_name' => $item->storeProduct->name ?? 'Unknown',
                    'quantity' => round($quantity, 2),
                    'unit_cost' => round($unitCost, 2),
                    'shipping' =>  round($shipping, 2),
                    'fees' => round($fees, 2),
                    'costing_per_product' => round($costingPerProduct, 2),
                    'sale_price' => round($salePrice, 2),
                    'profit_per_product' => round($profitPerProduct, 2),
                    'buy_price_asset' => round($buyPriceAsset, 2),
                    'sale_price_asset' => round($salePriceAsset, 2),
                    'sold_product' => round($soldQty, 2),
                    'sold_buy_product_price' => round($soldBuyProductPrice, 2),
                    'sold_product_price' => round($totalSoldProductPrice, 2),
                    'total_profit_product' => round($totalProfitProduct, 2),
                    'availble_stock' => round($availableStock, 2),
                    'availble_asset_buy_price' => round($availableAssetBuyPrice, 2),
                    'availble_asset_sale_price' => round($availableAssetSalePrice, 2),
                ];
            });
            // ->filter(function ($row) {
            //     // 🧹 Remove rows whose available stock is zero
            //     return $row['availble_stock'] != 0;
            // });

            // Sort per-product rows
            $tableData = $tableData->sortByDesc(fn($row) => $row['invoice_number'])->values();

            // Product-level totals
            $grands = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'grand_buy_price_asset' =>  round($tableData->sum('buy_price_asset'), 2),
                'grand_sale_price_asset' => round($tableData->sum('sale_price_asset'), 2),
                'grand_sold_product' => round($tableData->sum('sold_product'), 2),
                'grand_sold_buy_product_price' => round($tableData->sum('sold_buy_product_price'), 2),
                'grand_sold_product_price' => round($tableData->sum('sold_product_price'), 2),
                'grand_profit_product' => round($tableData->sum('total_profit_product'), 2),
                'grand_initial_stock' => round($tableData->sum('quantity'), 2),
                'grand_avaiable_stock' => round($tableData->sum('availble_stock'), 2),
                'grand_availble_asset_buy_price' => round($tableData->sum('availble_asset_buy_price'), 2),
                'grand_availble_asset_sale_price' => round($tableData->sum('availble_asset_sale_price'), 2),
            ];

            $allGrands[] = $grands;

            // Combine product rows and grand total row
            $allTableData = $allTableData->concat($tableData);
            $allTableData->push([
                'invoice_number' => "Grand Total ({$product->name})",
                'product_name' => '-',
                'quantity' => $grands['grand_initial_stock'],
                'unit_cost' => '-',
                'shipping' => '-',
                'fees' => '-',
                'costing_per_product' => '-',
                'sale_price' => '-',
                'profit_per_product' => '-',
                'buy_price_asset' => $grands['grand_buy_price_asset'],
                'sale_price_asset' => $grands['grand_sale_price_asset'],
                'sold_product' => $grands['grand_sold_product'],
                'sold_buy_product_price' => $grands['grand_sold_buy_product_price'],
                'sold_product_price' => $grands['grand_sold_product_price'],
                'total_profit_product' => $grands['grand_profit_product'],
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
                'buy_price_asset' =>  round(collect($allGrands)->sum('grand_buy_price_asset'), 2),
                'sale_price_asset' => round(collect($allGrands)->sum('grand_sale_price_asset'), 2),
                'sold_product' => round(collect($allGrands)->sum('grand_sold_product'), 2),
                'sold_buy_product_price' => round(collect($allGrands)->sum('grand_sold_buy_product_price'), 2),
                'sold_product_price' => round(collect($allGrands)->sum('grand_sold_product_price'), 2),
                'total_profit_product' => round(collect($allGrands)->sum('grand_profit_product'), 2),
                'availble_stock' => round(collect($allGrands)->sum('grand_avaiable_stock'), 2),
                'availble_asset_buy_price' => round(collect($allGrands)->sum('grand_availble_asset_buy_price'), 2),
                'availble_asset_sale_price' => round(collect($allGrands)->sum('grand_availble_asset_sale_price'), 2),
            ];

            $allTableData->push($finalGrand);
        }

        // 🧹 Eliminate rows where available stock = 0
        $allTableData = $allTableData->filter(function ($row) {
            return ! isset($row['availble_stock']) || $row['availble_stock'] != 0;
        })->values();

        return Inertia::render('store/reports/StockProductReport', [
            'productTypes' => $productTypes,
            'formattedDate' => $formattedDate,
            // 'products' => $products,     // ⬅️ for pagination links
            'tableData' => $allTableData,
            // 'grands' => $allGrands,
        ]);
    }

    // // Sub Category based
    // public function index(Request $request)
    // {
    //     // Get category and product IDs from user input
    //     $selectedCatId = $request->input('category_id');
    //     $selectedProductId = $request->input('product_id');

    //     // Get all product categories
    //     $productTypes = StoreProductType::select('id', 'name')->get();

    //     // Get all products for the selected category (if category selected)
    //     $products = collect();
    //     if ($selectedCatId) {
    //         $products = StoreProduct::where('store_product_type_id', $selectedCatId)
    //             ->select('id', 'name')
    //             ->get();
    //     }

    //     // Determine which product ID to use
    //     $productId = $selectedProductId ?? StoreStockMovement::orderBy('id', 'asc')->value('store_product_id');

    //     // Get all purchase movements (without storeStockItem)
    //     $stockMovements = StoreStockMovement::with(['storeProduct', 'storeStock'])
    //         ->where('source_type', 'purchase')
    //         ->where('store_product_id', $productId)
    //         ->get(['id', 'store_stock_id', 'store_product_id', 'change_quantity', 'source_data']);

    //     // Get all sales grouped by invoice
    //     $soldPerInvoice = StoreStockMovement::with('storeStock')
    //         ->where('source_type', 'sale')
    //         ->where('store_product_id', $productId)
    //         ->get()
    //         ->groupBy(fn ($item) => $item->storeStock->invoice_number ?? 'N/A')
    //         ->map(fn ($group) => $group->sum('change_quantity'));

    //     // Prepare dynamic sale price, sale price asset, and sold product price
    //     $dynamicSalePrice = 0;
    //     $dynamicSaleAsset = 0;
    //     $dynamicSoldProductPrice = 0;
    //     $totalSoldQty = 0;

    //     // Get all stock items for this product grouped by invoice number
    //     $stockItemsByInvoice = StoreStockItem::where('store_product_id', $productId)
    //         ->with('storeStock')
    //         ->get()
    //         ->groupBy(fn ($item) => $item->storeStock->invoice_number ?? 'N/A');

    //     // Compute per-invoice meta details
    //     // Compute per-invoice meta details
    //     $invoiceMetaData = $stockItemsByInvoice->map(function ($items, $invoice) {
    //         $latest = $items->sortByDesc('id')->first();
    //         $meta = json_decode($latest->price_meta, true) ?? [];

    //         $dynamicSoldProductPrice = 0;

    //         // Local array for max quantity per price for THIS invoice
    //         $priceMaxQty = [];

    //         // Recursive decode for previous chain
    //         $decodeMetaChain = function ($meta) use (&$decodeMetaChain, &$priceMaxQty) {
    //             if (! is_array($meta)) {
    //                 $decoded = json_decode($meta, true);
    //                 if (json_last_error() === JSON_ERROR_NONE) {
    //                     $meta = $decoded;
    //                 } else {
    //                     return;
    //                 }
    //             }

    //             if (isset($meta['quantity_sold'], $meta['old_price'])) {
    //                 $price = (float) $meta['old_price'];
    //                 $qty = (float) $meta['quantity_sold'];

    //                 // Only keep the highest quantity sold for the same old price
    //                 if (! isset($priceMaxQty[$price]) || $qty > $priceMaxQty[$price]) {
    //                     $priceMaxQty[$price] = $qty;
    //                 }
    //             }

    //             if (isset($meta['previous']) && $meta['previous']) {
    //                 $decodeMetaChain($meta['previous']);
    //             }
    //         };

    //         $decodeMetaChain($meta);

    //         // Calculate dynamicSoldProductPrice per invoice
    //         $dynamicSoldProductPrice = 0;
    //         foreach ($priceMaxQty as $price => $qty) {
    //             $dynamicSoldProductPrice += $price * $qty;
    //         }

    //         // Extract sale price and quantity details
    //         $dynamicSalePrice = (float) ($meta['new_price'] ?? $latest->sale_price ?? 0);
    //         $totalStock = (float) ($latest->quantity ?? 0);

    //         // Total sold quantity (sum of max quantities per price)
    //         $totalSoldQty = array_sum($priceMaxQty);

    //         $remainingQty = max(0, $totalStock - $totalSoldQty);
    //         $dynamicSaleAsset = $dynamicSoldProductPrice + ($remainingQty * $dynamicSalePrice);

    //         return [
    //             'dynamicSalePrice' => $dynamicSalePrice,
    //             'dynamicSaleAsset' => $dynamicSaleAsset,
    //             'dynamicSoldProductPrice' => $dynamicSoldProductPrice,
    //             'totalSoldQty' => $totalSoldQty,
    //         ];
    //     });

    //     // 🟢 Keep the original variables, just set defaults (so no code breaks)
    //     $dynamicSalePrice = 0;
    //     $dynamicSaleAsset = 0;
    //     $dynamicSoldProductPrice = 0;
    //     $totalSoldQty = 0;

    //     // You’ll now use $invoiceMetaData inside your $tableData map
    //     $tableData = $stockMovements->map(function ($item) use ($soldPerInvoice, $invoiceMetaData) {
    //         $invoiceNumber = $item->storeStock->invoice_number ?? 'N/A';
    //         $soldQty = $soldPerInvoice[$invoiceNumber] ?? 0;

    //         $meta = $invoiceMetaData[$invoiceNumber] ?? [
    //             'dynamicSalePrice' => 0,
    //             'dynamicSaleAsset' => 0,
    //             'dynamicSoldProductPrice' => 0,
    //             'totalSoldQty' => 0,
    //         ];

    //         // Assign per-invoice meta values
    //         $dynamicSalePrice = $meta['dynamicSalePrice'];
    //         $dynamicSaleAsset = $meta['dynamicSaleAsset'];
    //         $dynamicSoldProductPrice = $meta['dynamicSoldProductPrice'];
    //         $totalSoldQty = $meta['totalSoldQty'];

    //         // ✅ Rest of your logic untouched
    //         $sourceData = is_array($item->source_data)
    //             ? $item->source_data
    //             : json_decode($item->source_data, true);

    //         $slodQtyUpPrice = $soldQty - $totalSoldQty;
    //         $quantity = $item->change_quantity;
    //         $unitCost = $sourceData['unit_cost'] ?? 0;
    //         $shipping = $sourceData['shipping'] ?? 0;
    //         $fees = $sourceData['fees'] ?? 0;
    //         $costingPerProduct = $unitCost + $shipping + $fees;

    //         $salePrice = $dynamicSalePrice;
    //         $salePriceAsset = $dynamicSaleAsset;
    //         $profitPerProduct = $salePrice - $costingPerProduct;
    //         $buyPriceAsset = $quantity * $costingPerProduct;
    //         $soldBuyProductPrice = $soldQty * $costingPerProduct;
    //         $soldProductPrice = $slodQtyUpPrice * $salePrice;
    //         $totalSoldProductPrice = $dynamicSoldProductPrice + $soldProductPrice;
    //         $totalProfitProduct = $totalSoldProductPrice - $soldBuyProductPrice;
    //         $availableStock = $quantity - $soldQty;
    //         $availableAssetBuyPrice = $availableStock * $costingPerProduct;
    //         $availableAssetSalePrice = $availableStock * $salePrice;

    //         return [
    //             'invoice_number' => $invoiceNumber,
    //             'product_name' => $item->storeProduct->name ?? 'Unknown',
    //             'quantity' => $quantity,
    //             'unit_cost' => $unitCost,
    //             'shipping' => $shipping,
    //             'fees' => $fees,
    //             'costing_per_product' => $costingPerProduct,
    //             'sale_price' => $salePrice,
    //             'profit_per_product' => $profitPerProduct,
    //             'buy_price_asset' => $buyPriceAsset,
    //             'sale_price_asset' => $salePriceAsset,
    //             'sold_product' => $soldQty,
    //             'sold_buy_product_price' => $soldBuyProductPrice,
    //             'sold_product_price' => $totalSoldProductPrice,
    //             'total_profit_product' => $totalProfitProduct,
    //             'availble_stock' => $availableStock,
    //             'availble_asset_buy_price' => $availableAssetBuyPrice,
    //             'availble_asset_sale_price' => $availableAssetSalePrice,
    //         ];
    //     });

    //     // dd($tableData);

    //     // Compute totals
    //     $grands = [
    //         'grand_buy_price_asset' => $tableData->sum('buy_price_asset'),
    //         'grand_sale_price_asset' => $tableData->sum('sale_price_asset'),
    //         'grand_sold_product' => $tableData->sum('sold_product'),
    //         'grand_sold_buy_product_price' => $tableData->sum('sold_buy_product_price'),
    //         'grand_sold_product_price' => $tableData->sum('sold_product_price'),
    //         'grand_profit_product' => $tableData->sum('total_profit_product'),
    //         'grand_initial_stock' => $tableData->sum('quantity'),
    //         'grand_avaiable_stock' => $tableData->sum('availble_stock'),
    //         'grand_availble_asset_buy_price' => $tableData->sum('availble_asset_buy_price'),
    //         'grand_availble_asset_sale_price' => $tableData->sum('availble_asset_sale_price'),
    //     ];

    //     return Inertia::render('store/reports/StockProductReport', [
    //         'productTypes' => $productTypes,
    //         'products' => $products,
    //         'tableData' => $tableData,
    //         'grands' => $grands,
    //     ]);
    // }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
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
