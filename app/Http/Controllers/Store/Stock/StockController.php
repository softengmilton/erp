<?php

namespace App\Http\Controllers\Store\Stock;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\Stock\StoreStockRequest;
use App\Models\StoreProduct;
use App\Models\StoreStock;
use App\Traits\MediaMan;
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
                'storeStockMovements'
            ])
            ->withCount([
                'storeStockItems as total_quantity' => fn($query) =>
                $query->select(DB::raw('COALESCE(SUM(quantity), 0)'))
            ])
            ->withCount([
                'storeStockMovements as total_movements' => fn($query) =>
                $query->where('source_type', 'sale')
                    ->select(DB::raw('COALESCE(SUM(change_quantity), 0)'))
            ])
            ->orderBy('id', 'desc')
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
    public function show(string $id)
    {

        return Inertia::render('store/stock/stock/Show');
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
