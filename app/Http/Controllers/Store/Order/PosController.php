<?php

namespace App\Http\Controllers\Store\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class PosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Get stock ID from session
            $stock = Session::get('stock_id');
            // $stock = $request->input('stock_number');
            // Session::put('stock_number', $stock);
            // $stock = Session::get('stock_number');

            // Load the stock based on session or fallback to latest
            if (!$stock) {
                $stock = \App\Models\StoreStock::with([
                    'storeStockItems.storeProduct.storeProductType.primaryImage'
                ])->latest()->first();
            } else {
                $stock = \App\Models\StoreStock::with([
                    'storeStockItems.storeProduct.storeProductType.primaryImage'
                ])->where('invoice_number', $stock)->first();
            }

            // dd($stockNumber);
            // If no stock found, redirect
            if (!$stock) {
                return Redirect::back()->with('toast', [
                    'type' => 'error',
                    'message' => 'No stock found.',
                ]);
            }

            // Get all stock invoice numbers
            $stockNumbers = \App\Models\StoreStock::pluck('invoice_number')->toArray();

            // Get product IDs from stock items
            $stockItemIds = $stock->storeStockItems->pluck('store_product_id');

            // Load stock movements for sales
            $movements = \App\Models\StoreStockMovement::where('store_stock_id', $stock->id)
                ->whereIn('store_product_id', $stockItemIds)
                ->where('source_type', 'sale')
                ->get()
                ->groupBy('store_product_id');

            // Calculate current quantity for each item
            foreach ($stock->storeStockItems as $item) {
                $item->current_quantity = $movements[$item->store_product_id]->sum('change_quantity') ?? 0;
            }


            // Get product type IDs used in current stock items
            $productTypeIds = $stock->storeStockItems
                ->pluck('storeProduct.store_product_type_id')
                ->unique()
                ->filter()
                ->values();

            // Fetch product types related to current stock, with image
            $productTypes = \App\Models\StoreProductType::with('primaryImage')
                ->select('id', 'name')
                ->whereIn('id', $productTypeIds)
                ->get();

            // Return data to Vue via Inertia
            return Inertia::render('store/order/Pos', [
                'stockNumbers' => $stockNumbers,
                'stock' => $stock,
                'productTypes' => $productTypes,
            ]);
        } catch (\Throwable $e) {

            // dd($e);
            return Redirect::back()->with('toast', [
                'type' => 'error',
                'message' => 'Failed to load stock data: ' . $e->getMessage(),
            ]);
        }
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
