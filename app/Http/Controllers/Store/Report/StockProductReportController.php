<?php

namespace App\Http\Controllers\Store\Report;

use App\Http\Controllers\Controller;
use App\Models\StoreStockMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockProductReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        // Get product Id
        $productId = StoreStockMovement::orderBy('id', 'asc')->value('store_product_id');
        // dd($productId);

        // Get all purchase movements with related product, stock, and stock item info
        $stockMovement = StoreStockMovement::with(['storeProduct', 'storeStock', 'storeStockItem'])
            ->where('source_type', 'purchase')
            ->where('store_product_id', $productId)
            ->get(['id', 'store_stock_id', 'store_product_id', 'change_quantity', 'source_data']);

        // dd($stockMovement);

        // Get total sold per invoice using relationship
        $soldPerInvoice = StoreStockMovement::with('storeStock')
            ->where('source_type', 'sale')
            ->where('store_product_id', $productId)
            ->get()
            ->groupBy(fn ($items) => $items->storeStock->invoice_number ?? 'N/A')
            ->map(fn ($group) => $group->sum('change_quantity'));

        // dd($soldPerInvoice);

        // dd($stockMovement);
        $tableData = $stockMovement->map(function ($item, $index) use ($soldPerInvoice) {
            // Decode JSON
            $sourceData = is_array($item->source_data) ? $item->source_data : json_decode($item->source_data, true);

            $quantity = $item->change_quantity;

            $costing_per_product = ($sourceData['unit_cost'] ?? 0) + ($sourceData['shipping'] ?? 0) + ($sourceData['fees'] ?? 0);
            $sale_price = $item->storeStockItem->sale_price;
            $profit_per_product = $sale_price - $costing_per_product;

            $buy_price_asset = $quantity * $costing_per_product;
            $sale_price_asset = $quantity * $sale_price;

            $sold_product_price = $soldPerInvoice[$item->storeStock->invoice_number] * $sale_price;

            $total_profit_product = $sale_price_asset - $buy_price_asset;

            $availble_stock = $quantity - $soldPerInvoice[$item->storeStock->invoice_number];

            $availble_asset_buy_price = $availble_stock * $costing_per_product;
            $availble_asset_sale_price = $availble_stock * $sale_price;

            return [
                'invoice_number' => $item->storeStock->invoice_number ?? 'N/A',
                'product_name' => $item->storeProduct->name ?? 'Unknown',
                'quantity' => $item->change_quantity,
                'unit_cost' => $sourceData['unit_cost'] ?? 0,
                'shipping' => $sourceData['shipping'] ?? 0,
                'fees' => $sourceData['fees'] ?? 0,
                'costing_per_product' => $costing_per_product,
                'sale_price' => $item->storeStockItem->sale_price ?? 0,
                'profit_per_product' => $profit_per_product,

                'buy_price_asset' => $buy_price_asset,
                'sale_price_asset' => $sale_price_asset,

                'sold_product' => $soldPerInvoice[$item->storeStock->invoice_number] ?? 0,
                'sold_product_price' => $sold_product_price,
                'total_profit_product' => $total_profit_product,
                'availble_stock' => $availble_stock,

                'availble_asset_buy_price' => $availble_asset_buy_price,
                'availble_asset_sale_price' => $availble_asset_sale_price,
            ];
        });

        // dd($tableData);

        return Inertia::render('store/reports/StockProductReport', [
            'tableData' => $tableData,
        ]);
    }

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
