<?php

namespace App\Http\Controllers\Store\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Only update if new value sent
            if ($request->filled('stock_number')) {
                Session::put('stock_number', $request->input('stock_number'));
            }

            $stock = Session::get('stock_number');

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

            // If no stock found, redirect
            if (!$stock) {
                return Redirect::back()->with('toast', [
                    'type' => 'error',
                    'message' => 'No stock found.',
                ]);
            }

            // Get all stock invoice numbers
            $stockNumbers = \App\Models\StoreStock::orderByDesc('id')->pluck('invoice_number')->toArray();

            // Get product IDs from stock items
            $stockItemIds = $stock->storeStockItems->pluck('store_product_id');

            // Load stock movements for sales and group by product
            $movements = \App\Models\StoreStockMovement::where('store_stock_id', $stock->id)
                ->whereIn('store_product_id', $stockItemIds)
                ->where('source_type', 'sale')
                ->get()
                ->groupBy('store_product_id');

            // Calculate current quantity for each item
            foreach ($stock->storeStockItems as $item) {
                $soldQty = $movements->get($item->store_product_id, collect())->sum('change_quantity');
                $item->current_quantity = $item->quantity - $soldQty;
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

            $customers = \App\Models\Customer::select('id', 'name', 'phone', 'email')
                ->get();

            // Return data to Vue via Inertia
            return Inertia::render('store/order/Pos', [
                'stockNumbers' => $stockNumbers,
                'stock' => $stock,
                'productTypes' => $productTypes,
                'customers' => $customers,
            ]);
        } catch (\Throwable $e) {
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


    public function store(Request $request)
    {
        // Validate request data
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:store_stock_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,bkash,nagad',
            'total' => 'required|numeric|min:0',
            'paid' => 'required|numeric|min:0',
            'due' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'adjustment' => 'required|numeric',
            'stock_number' => 'required|exists:store_stocks,invoice_number',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        DB::beginTransaction();
        try {
            // 1. Create the order
            $order = \App\Models\StoreOrder::create([
                'order_number' => 'ORD-' . date('YmdHis'),
                'customer_type' => $validated['customer_id'] ? 'registered' : 'walking',
                'customer_id' => $validated['customer_id'] ?? null,
                'total_amount' => $validated['total'],
                'paid_amount' => $validated['paid'],
                'due_amount' => $validated['due'],
                'payment_status' => $validated['due'] > 0 ? 'due' : 'paid',
                'payment_method' => $validated['payment_method'],
                'discount' => $validated['discount'],
                'adjustment' => $validated['adjustment'],

            ]);

            // 2. Process each item
            foreach ($validated['items'] as $item) {
                $stockItem = \App\Models\StoreStockItem::find($item['id']);

                // Create order item
                $order->storeOrderItems()->create([
                    'store_product_id' => $stockItem->store_product_id,
                    'store_stock_id' => $stockItem->store_stock_id,
                    'quantity' => $item['quantity'],
                    'sale_price' => $stockItem->sale_price,
                ]);

                // Record stock movement
                \App\Models\StoreStockMovement::create([
                    'store_stock_id' => $stockItem->store_stock_id,
                    'store_product_id' => $stockItem->store_product_id,
                    'change_quantity' => $item['quantity'], // Negative for sales
                    'source_type' => 'sale',
                    'source_data' => json_encode([ // Manually encode
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                    ]),
                ]);
            }

            DB::commit();

            return redirect()->back()->with([
                'toast' => [
                    'type' => 'success',
                    'message' => 'Order #' . $order->order_number . ' created successfully!'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'toast' => [
                    'type' => 'error',
                    'message' => 'Error: ' . $e->getMessage()
                ]
            ]);
        }
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
