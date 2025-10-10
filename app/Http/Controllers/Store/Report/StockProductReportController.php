<?php

namespace App\Http\Controllers\Store\Report;

use App\Http\Controllers\Controller;
use App\Models\StoreProduct;
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

        // Product Id
        $productId = StoreStockMovement::orderBy('id', 'asc')->value('store_product_id');
        // dd($productId);

        // Get all purchase movements with related product and stock info
        $stockMovement = StoreStockMovement::with(['storeProduct', 'storeStock'])
            ->where('source_type', 'purchase')
            ->where('store_product_id', $productId)
            ->get(['id', 'store_stock_id', 'store_product_id', 'change_quantity', 'source_data']);

        // dd($stockMovement);
        $tableData = $stockMovement->map(function ($item, $index) {
            // Decode JSON
            $sourceData = is_array($item->source_data) ? $item->source_data : json_decode($item->source_data, true);

            $costing_per_product = ($sourceData['unit_cost'] ?? 0) + ($sourceData['shipping'] ?? 0) + ($sourceData['fees'] ?? 0);

            return [
                'invoice_number' => $item->storeStock->invoice_number ?? 'N/A',
                'product_name' => $item->storeProduct->name ?? 'Unknown',
                'quantity' => $item->change_quantity,
                'unit_cost' => $sourceData['unit_cost'] ?? 0,
                'shipping' => $sourceData['shipping'] ?? 0,
                'fees' => $sourceData['fees'] ?? 0,
                'costing_per_product' => $costing_per_product,
            ];
        });

        // $stockMovements = StoreStockMovement::with(['storeProduct', 'storeStock'])
        //     ->where('source_type', 'purchase')
        //     ->get(['id', 'store_stock_id', 'store_product_id', 'change_quantity', 'source_data']);

        // // dd($stockMovements);

        // $tableData = $stockMovements->map(function ($item, $index) {
        //     return [
        //         'invoice_number' => $item->storeStock->invoice_number ?? 'N/A',
        //         'product_name' => $item->storeProduct->name ?? 'Unknown',
        //         'quantity' => $item->change_quantity,
        //     ];
        // });

        // dd($tableData);

        return Inertia::render('store/reports/StockProductReport', [
            'tableData' => $tableData,
        ]);

        // // Get all products with related stock movements
        // $products = StoreProduct::with('storeStockMovements')->get();

        // // dd($products);

        // // Calculate available stock per product
        // $stockSummary = $products->map(function ($product) {
        //     $totalPurchased = $product->storeStockMovements
        //         ->where('source_type', 'purchase')
        //         ->sum('change_quantity');

        //     $totalSold = $product->storeStockMovements
        //         ->where('source_type', 'sale')
        //         ->sum('change_quantity');

        //     return [
        //         'product_id' => $product->id,
        //         'product_name' => $product->name,
        //         'total_purchased' => $totalPurchased,
        //         'total_sold' => $totalSold,
        //         'available_stock' => $totalPurchased - $totalSold,
        //     ];
        // });
        // dd($stockSummary);

        // return Inertia::render('store/reports/StockProductReport', [
        //     'stockSummary' => $stockSummary,
        // ]);
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
