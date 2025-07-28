<?php

namespace App\Http\Controllers\Store\Expense;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Models\StoreExpenseType;
use Exception;
use Illuminate\Support\Facades\redirect;

class TypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $expenseTypes = StoreExpenseType::orderByDesc('id')->paginate(10);
        return Inertia::render('store/expense/type/Index', [
            'expenseTypes' => $expenseTypes
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
                'name' => 'required|string|max:255|unique:store_expense_types',
                'description' => 'nullable|string|max:255'
            ]);
            StoreExpenseType::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
            ]);
            return Redirect::back()->with('toast', [
                'type' => 'success',
                'message' => 'Expense Type created successfully'
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
            $expenseType = StoreExpenseType::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255|unique:store_expense_types,name,' . $id,
                'description' => 'nullable|string|max:255',
            ]);

            $expenseType->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
            ]);

            return redirect::back()->with('toast', [
                'type' => 'success',
                'message' => 'Expense Type updated successfully',
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
            $expenseType = StoreExpenseType::findOrFail($id);

            if ($expenseType->storeExpenses()->exists()) {
                return Redirect::back()->with('toast', [
                    'type' => 'warning',
                    'message' => 'Cannot delete expense type because it is associated with expenses',
                ]);
            }
            $expenseType->delete();

            return redirect::back()->with('toast', [
                'type' => 'success',
                'message' => 'Expense Type deleted successfully',
            ]);
        } catch (\Exception $e) {
            return Redirect::back()->with('toast', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
