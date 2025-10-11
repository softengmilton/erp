<?php

namespace App\Http\Controllers\Store\Report;

use App\Http\Controllers\Controller;
use App\Models\StoreProduct;
use App\Models\StoreProductType;
use App\Models\StoreStockMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockProductReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        // Get category and product IDs from user input
        $selectedCatId = $request->input('category_id');
        $selectedProductId = $request->input('product_id');

        // Get all product categories
        $productTypes = StoreProductType::select('id', 'name')->get();

        // Get all products for the selected category (if category selected)
        $products = collect(); // empty by default
        if ($selectedCatId) {
            $products = StoreProduct::where('store_product_type_id', $selectedCatId)
                ->select('id', 'name')
                ->get();
        }

        // Determine which product ID to use
        // $productId = StoreStockMovement::orderBy('id', 'asc')->value('store_product_id');
        // $productId = $selectedProductId ? $selectedProductId : StoreStockMovement::orderBy('id', 'asc')->value('store_product_id');
        // $productId = $selectedProductId ?? StoreStockMovement::orderBy('id', 'asc')->value('store_product_id');
        $productId = '1';

        // Get all purchase movements with related product, stock, and stock item info
        $stockMovement = StoreStockMovement::with(['storeProduct', 'storeStock', 'storeStockItem'])
            ->where('source_type', 'purchase')
            ->where('store_product_id', $productId)
            ->get(['id', 'store_stock_id', 'store_product_id', 'change_quantity', 'source_data']);

        dd($stockMovement);

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

            $sold_buy_product_price = $soldPerInvoice[$item->storeStock->invoice_number] * $costing_per_product;
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
                'sold_buy_product_price' => $sold_buy_product_price,
                'sold_product_price' => $sold_product_price,
                'total_profit_product' => $total_profit_product,
                'availble_stock' => $availble_stock,

                'availble_asset_buy_price' => $availble_asset_buy_price,
                'availble_asset_sale_price' => $availble_asset_sale_price,
            ];
        });

        // Now compute all grand totals using collection helpers
        $grands = [
            'grand_buy_price_asset' => $tableData->sum('buy_price_asset'),
            'grand_sale_price_asset' => $tableData->sum('sale_price_asset'),
            'grand_sold_product' => $tableData->sum('sold_product'),
            'grand_sold_buy_product_price' => $tableData->sum('sold_buy_product_price'),
            'grand_sold_product_price' => $tableData->sum('sold_product_price'),
            'grand_profit_product' => $tableData->sum('total_profit_product'),
            'grand_initial_stock' => $tableData->sum('quantity'),
            'grand_avaiable_stock' => $tableData->sum('availble_stock'),
            'grand_availble_asset_buy_price' => $tableData->sum('availble_asset_buy_price'),
            'grand_availble_asset_sale_price' => $tableData->sum('availble_asset_sale_price'),
        ];

        // dd($tableData);
        // dd($grands);

        return Inertia::render('store/reports/StockProductReport', [
            'productTypes' => $productTypes,
            'products' => $products,
            'tableData' => $tableData,
            'grands' => $grands,
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
