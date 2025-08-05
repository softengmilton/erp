<?php

namespace App\Http\Controllers\Store\Stock;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\Stock\StoreStockRequest;
use App\Models\StoreProduct;
use App\Models\StoreStock;
use App\Models\StoreStockItem;
use App\Traits\MediaMan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class StockController extends Controller
{
    use MediaMan;
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $stocks = StoreStock::query()
            ->with([
                'storeStockItems.storeProduct',
                'storeStockMovements',
            ])
            ->withCount([
                'storeStockItems as total_quantity' => fn($query) =>
                $query->select(DB::raw('COALESCE(SUM(quantity), 0)')),

                'storeStockMovements as total_movements' => fn($query) =>
                $query->whereRaw("LOWER(source_type) = 'sale'")
                    ->select(DB::raw('COALESCE(SUM(change_quantity), 0)')),
            ])
            ->orderByDesc('id')
            ->paginate(10);

        return Inertia::render('store/stock/stock/Index', [
            'stocks' => $stocks,
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = StoreProduct::query()
            ->with('primaryImage')
            ->select('id', 'name')
            ->orderBy('id', 'desc')
            ->get();

        $nextId = StoreStock::max('id') + 1;
        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        return Inertia::render('store/stock/stock/Create', [
            'products' => $products,
            'invoice_number' => $invoiceNumber,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'invoice_number' => 'required',
                'supplier_name' => 'required|string|max:255',
                'shipping_cost' => 'nullable|numeric|min:0',
                'other_fees' => 'nullable|numeric|min:0',
                'total_cost' => 'required|numeric|min:0',
                'note' => 'nullable',
                'document' => 'nullable',
                'products' => 'required|array|min:1',
                'products.*.id' => 'required|exists:store_products,id',
                'products.*.name' => 'sometimes|string',
                'products.*.quantity' => 'required|integer|min:1',
                'products.*.unit_cost' => 'required|numeric|min:0.1',
                'products.*.shipping_cost_per_unit' => 'nullable|numeric|min:0',
                'products.*.other_fees_per_unit' => 'nullable|numeric|min:0',
                'products.*.unit_landed_cost' => 'nullable|numeric|min:0',
                'products.*.shipping_cost' => 'nullable|numeric|min:0',
                'products.*.other_fees' => 'nullable|numeric|min:0',
                'products.*.total_cost' => 'nullable|numeric',
                'products.*.sale_price' => 'required|numeric|min:0',
            ]);

            // Begin transaction
            DB::beginTransaction();
            // Create stock record
            $stock = StoreStock::create([
                'invoice_number' => $validated['invoice_number'],
                'supplier_name' => $validated['supplier_name'],
                'shipping_cost' => $validated['shipping_cost'] ?? 0,
                'other_fees' => $validated['other_fees'] ?? 0,
                'total_cost' => $validated['total_cost'],
                'note' => $validated['note'],
            ]);

            // Handle document upload if exists
            if ($request->hasFile('document')) {
                $image = $this->storeFile($request->file('document'), 'store_stock_document');
                $stock->primaryImage()->create([...$image, 'media_role' => 'store_stock_document']);
            }

            // Create stock items
            foreach ($validated['products'] as $productData) {
                $stock->storeStockItems()->create([
                    'store_product_id' => $productData['id'],
                    'quantity' => $productData['quantity'],
                    'unit_cost' => $productData['unit_cost'],
                    'shipping_cost_unit' => $productData['shipping_cost_per_unit'] ?? 0,
                    'other_fees_unit' => $productData['other_fees_per_unit'] ?? 0,
                    'total_cost' => $productData['total_cost'] ?? 0,
                    'sale_price' => $productData['sale_price'],
                ]);
            }
            // Commit transaction
            DB::commit();
            return Redirect::route('store.stocks.index')->with('toast', [
                'type' => 'success',
                'message' => 'Stock Stored successfully',
            ]);
        } catch (\Exception $e) {
            // Rollback transaction in case of error
            DB::rollBack();
            return Redirect::back()->with('toast', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($stock)
    {
        $stock = StoreStock::with([
            'storeStockItems.storeProduct.primaryImage',
            'storeStockMovements'
        ])
            ->where('invoice_number', $stock)
            ->firstOrFail();

        // Get product IDs
        $productIds = $stock->storeStockItems->pluck('store_product_id');

        // Get sold quantities from movements
        $soldQuantities = DB::table('store_stock_movements')
            ->where('store_stock_id', $stock->id)
            ->whereRaw("LOWER(source_type) = 'sale'")
            ->whereIn('store_product_id', $productIds)
            ->select('store_product_id', DB::raw('SUM(change_quantity) as quantity_sold'))
            ->groupBy('store_product_id')
            ->pluck('quantity_sold', 'store_product_id');

        // Initialize stock stats
        $stockLevels = [
            'inStock' => 0,
            'lowStock' => 0,
            'outOfStock' => 0,
        ];

        // Attach sold quantity and calculate stock level status
        foreach ($stock->storeStockItems as $item) {
            $product = $item->storeProduct;
            $item->quantity_sold = (int) ($soldQuantities[$product->id] ?? 0);

            if ($item->quantity <= $item->quantity_sold) {
                $stockLevels['outOfStock']++;
            } elseif ($item->quantity <= $product->low_stock_alert && $item->quantity > $item->quantity_sold) {
                $stockLevels['lowStock']++;
                $stockLevels['inStock']++;
            } else {
                $stockLevels['inStock']++;
            }
        }

        // Load movement and sales totals
        $stock->loadCount([
            'storeStockMovements as total_movements' => fn($query) =>
            $query->whereRaw("LOWER(source_type) = 'sale'")
                ->select(DB::raw('COALESCE(SUM(change_quantity), 0)')),
            'storeStockItems as total_sale' => fn($query) =>
            $query->where('sale_price', '>', 0)
                ->select(DB::raw('COALESCE(SUM(sale_price * quantity), 0)')),
        ]);

        return Inertia::render('store/stock/stock/Show', [
            'stock' => $stock,
            'stats' => [
                'totalProducts' => $stock->storeStockItems->count(),
                ...$stockLevels,
            ],
        ]);
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
    public function destroy($stock)
    {
        try {
            $stock = StoreStock::findOrFail($stock);
            $stock->delete();

            return Redirect::route('store.stocks.index')->with('toast', [
                'type' => 'success',
                'message' => 'Stock deleted successfully.',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with('toast', [
                'type' => 'error',
                'message' => 'Failed to delete stock: ' . $e->getMessage(),
            ]);
        }
    }
    /**
     * Update the sale price of a product in stock.
     */


    public function updateStockProductPrice(Request $request, $stock, $product)
    {
        try {
            // dd($request->all(), $stock, $product);
            // $validated = $request->validate([
            //     'sale_price' => 'required|numeric|min:0',
            //     'note' => 'nullable|string|max:255',
            // ]);

            // $stockItem = StoreStockItem::query()
            //     ->where([
            //         'store_stock_id' => $stock,
            //         'store_product_id' => $product,
            //     ])->first();

            // dd($stockItem);
            dd($request->all(), $stock, $product);
            $stockItem = StoreStockItem::query()
                ->where('store_stock_id', $stock)
                ->where('store_product_id', $product)
                ->first();
            dd($stockItem);



            if (!$stockItem) {
                return Redirect::back()->with('toast', [
                    'type' => 'error',
                    'message' => 'Stock item not found.',
                ]);
            }

            $oldPrice = $stockItem->sale_price;

            // Count how many items were sold at the old price
            $quantitySold = DB::table('store_order_items')
                ->where('store_stock_id', $stock)
                ->where('store_product_id', $product)
                ->where('sale_price', $oldPrice)
                ->sum('quantity');

            $priceChangeSummary = $quantitySold > 0
                ? "{$oldPrice}*{$quantitySold}x"
                : "No items sold at old price.";

            // Update to new sale price
            $stockItem->update([
                'sale_price' => $validated['sale_price'],
                'adjustment_data' => json_encode([
                    'old_price' => $oldPrice,
                    'new_price' => $validated['sale_price'],
                    'quantity_sold' => $quantitySold,
                    'changed_at' => now(),
                ]),
            ]);

            return Redirect::back()->with('toast', [
                'type' => 'success',
                'message' => 'Product price updated successfully.',
                'summary' => $priceChangeSummary,
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with('toast', [
                'type' => 'error',
                'message' => 'Failed to update product price: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Adjust stock product quantity.
     */
    public function adjustStockProduct(Request $request, $stock, $product)
    {
        try {
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
