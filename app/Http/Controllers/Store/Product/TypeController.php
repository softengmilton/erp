<?php

namespace App\Http\Controllers\Store\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Models\StoreProductType;
use Exception;

class TypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productTypes = StoreProductType::orderByDesc('id')->paginate(10);
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
            return redirect()->back()->with('success', 'Product Type created successfully.');
        } catch (Exception $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create product type. Please try again.');
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
            return redirect()->back()->with('success', 'Product Type updated successfully.');
        } catch (Exception $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update product type. Please try again.');
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $productType = StoreProductType::findOrFail($id);

            if ($productType->products()->exists()) {
                return redirect()->back()->with('error', 'Cannot delete product type because it is associated with products.');
            }
            $productType->delete();
            return redirect()->back()->with('success', 'Product Type deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete product type. Please try again.');
        }
    }
}
