<?php

namespace App\Http\Controllers\Store\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Models\StoreProductType;
use App\Traits\MediaMan;
use Exception;
use Illuminate\Support\Facades\Redirect;

class TypeController extends Controller
{
    use MediaMan;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productTypes = StoreProductType::with('primaryImage')->orderByDesc('id')->paginate(10);
        return Inertia::render('store/product/type/Index', [
            'productTypes' => $productTypes
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
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:store_product_types,name',
                'description' => 'nullable|string|max:255',
            ]);
            $productType = StoreProductType::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
            ]);
            if ($request->hasFile('image')) {
                $image = $this->storeFile($request->file('image'), 'store_product_type_image');
                $productType->primaryImage()->create([...$image, 'media_role' => 'store_product_type_image']);
            }
            $productType->save();

            return Redirect::back()->with('toast', [
                'type' => 'success',
                'message' => 'Product Type created successfully',
            ]);
        } catch (Exception $e) {
            throw $e;
        } catch (\Exception $e) {
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
            $productType = StoreProductType::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255|unique:store_product_types,name,' . $id,
                'description' => 'nullable|string|max:255',
            ]);
            $productType->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
            ]);
            if ($request->hasFile('image')) {
                $productType->primaryImage()->delete();
                $image = $this->storeFile($request->file('image'), 'store_product_type_image');
                $productType->primaryImage()->create([...$image, 'media_role' => 'store_product_type_image']);
            } else {
                $productType->primaryImage()->delete();
            }
            $productType->update();
            return Redirect::back()->with('toast', [
                'type' => 'success',
                'message' => 'Product Type updated successfully',
            ]);
        } catch (Exception $e) {
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
            $productType = StoreProductType::findOrFail($id);

            if ($productType->storeProducts()->exists()) {
                return Redirect::back()->with('toast', [
                    'type' => 'warning',
                    'message' => 'Cannot delete product type because it is associated with products',
                ]);
            }
            $productType->delete();
            $productType->primaryImage()->delete();
            return Redirect::back()->with('toast', [
                'type' => 'success',
                'message' => 'Product Type deleted successfully',
            ]);
        } catch (\Exception $e) {
            return Redirect::back()->with('toast', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
