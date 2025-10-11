<?php

namespace App\Http\Controllers\Store\Report;

use App\Http\Controllers\Controller;
use App\Models\StoreProduct;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TotalAssetController extends Controller
{
    public function index()
    {
        // eager-load movements & related stock items to avoid N+1
        $products = StoreProduct::with([
            'storeStockMovements.storeStock',
            'storeStockMovements.storeStockItem',
        ])->get(['id', 'name']);

        // produce rows per product -> per purchase
        $rowsPerProduct = $products->map(function ($product) {
            $movements = $product->storeStockMovements;

            // sales for THIS product grouped by invoice_number
            $soldPerInvoice = $movements
                ->where('source_type', 'sale')
                ->groupBy(fn ($m) => $m->storeStock->invoice_number ?? 'N/A')
                ->map(fn ($group) => $group->sum('change_quantity')); // invoice_number => sold_qty

            // process purchase movements for THIS product
            $purchaseRows = $movements
                ->where('source_type', 'purchase')
                ->map(function ($purchase) use ($soldPerInvoice, $product) {
                    $invoiceNumber = $purchase->storeStock->invoice_number ?? 'N/A';
                    // sold qty for this invoice and this product (product scope preserved)
                    $soldQty = $soldPerInvoice->get($invoiceNumber, 0);

                    $sourceData = is_array($purchase->source_data)
                        ? $purchase->source_data
                        : json_decode($purchase->source_data, true);

                    $quantity = $purchase->change_quantity;
                    $unit_cost = $sourceData['unit_cost'] ?? 0;
                    $shipping = $sourceData['shipping'] ?? 0;
                    $fees = $sourceData['fees'] ?? 0;

                    $costing_per_product = $unit_cost + $shipping + $fees;
                    $sale_price = $purchase->storeStockItem->sale_price ?? 0;

                    $available_stock = $quantity - $soldQty;
                    if ($available_stock < 0) {
                        $available_stock = 0;
                    } // safety

                    $available_asset_buy_price = $available_stock * $costing_per_product;
                    $available_asset_sale_price = $available_stock * $sale_price;

                    return [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'invoice_number' => $invoiceNumber,
                        'purchase_quantity' => $quantity,
                        'sold_qty_on_invoice' => $soldQty,
                        'available_stock' => $available_stock,
                        'available_asset_buy_price' => $available_asset_buy_price,
                        'available_asset_sale_price' => $available_asset_sale_price,
                    ];
                });

            return $purchaseRows; // collection of this product's purchase rows
        });

        // flatten to single collection of rows (one row per purchase)
        $tableData = $rowsPerProduct->flatten(1);

        // grand totals
        $grands = [
            'grand_availble_asset_buy_price' => $tableData->sum('available_asset_buy_price'),
            'grand_availble_asset_sale_price' => $tableData->sum('available_asset_sale_price'),
        ];

        return Inertia::render('store/reports/Assets', [
            'grands' => $grands,
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
