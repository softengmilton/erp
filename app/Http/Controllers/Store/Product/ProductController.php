<?php

namespace App\Http\Controllers\Store\Product;

use App\Http\Controllers\Controller;
use App\Models\StoreProduct;
use App\Models\StoreProductType;
use App\Traits\MediaMan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class ProductController extends Controller
{
    use MediaMan;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productTypes = StoreProductType::orderByDesc('id')->get();
        $products = StoreProduct::with('primaryImage', 'storeProductType')->orderByDesc('id')->paginate(10);

        return Inertia::render('store/product/product/Index', [
            'products' => $products,
            'productTypes' => $productTypes,
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
        // dd($request->all());
        try {
            $request->validate([
                'name' => 'required',
                'barcode' => 'required',
                'description' => 'nullable',
                'unit' => 'required',
                'low_stock_alert' => 'required',
                'store_product_type_id' => 'required',
                'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            ]);
            DB::beginTransaction();
            $product = StoreProduct::create([
                'name' => $request->name,
                'slug' => \Str::slug($request->name),
                'barcode' => $request->barcode,
                'description' => $request->description,
                'unit' => $request->unit,
                'low_stock_alert' => $request->low_stock_alert,
                'store_product_type_id' => $request->store_product_type_id,
            ]);
            // dd($product);
            if ($request->hasFile('image')) {
                $image = $this->storeFile($request->file('image'), 'store_product_image');
                $product->primaryImage()->create([...$image, 'media_role' => 'store_product_image']);
            }
            $product->save();
            DB::commit();
            return Redirect::back()->with('toast', [
                'type' => 'success',
                'message' => 'Product created successfully',
            ]);
        } catch (Exception $e) {
            DB::rollback();
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
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:store_products,name,' . $id,
                'barcode' => 'required',
                'description' => 'nullable',
                'unit' => 'required',
                'low_stock_alert' => 'required',
                'store_product_type_id' => 'required',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);
            $product = StoreProduct::findOrFail($id);
            DB::beginTransaction();
            $product->update([
                'name' => $request->name,
                'slug' => \Str::slug($request->name),
                'barcode' => $request->barcode,
                'description' => $request->description,
                'unit' => $request->unit,
                'low_stock_alert' => $request->low_stock_alert,
                'store_product_type_id' => $request->store_product_type_id,
            ]);
            if ($request->hasFile('image')) {
                $product->primaryImage()->delete();
                $image = $this->storeFile($request->file('image'), 'store_product_image');
                $product->primaryImage()->create([...$image, 'media_role' => 'store_product_image']);
            } else {
                $product->primaryImage()->delete();
            }
            $product->update();
            DB::commit();
            return Redirect::back()->with('toast', [
                'type' => 'success',
                'message' => 'Product updated successfully',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return Redirect::back()->with('toast', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $product = StoreProduct::findOrFail($id);
            $product->delete();
            $product->primaryImage()->delete();
            return Redirect::back()->with('toast', [
                'type' => 'success',
                'message' => 'Product deleted successfully',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with('toast', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
