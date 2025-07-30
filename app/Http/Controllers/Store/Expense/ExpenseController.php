<?php

namespace App\Http\Controllers\Store\Expense;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Exception;
use App\Traits\MediaMan;
use App\Models\StoreExpenseType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\StoreExpense;
use Illuminate\Support\Facades\Redirect;

class ExpenseController extends Controller
{
    use MediaMan;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $expenses = StoreExpense::with('storeExpenseType')->paginate(10);
        $expenseTypes = StoreExpenseType::orderByDesc('id')->get();

        return Inertia::render('store/expense/expense/Index', [
            'expenses' => $expenses,
            'expenseTypes' => $expenseTypes,
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
        $incurredBy = Auth::id();
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:store_expenses',
                'description' => 'nullable|string',
                'amount' => 'required|numeric',
                'attachment' => 'required|max:2048',
            ]);

            DB::beginTransaction();
            $expense = StoreExpense::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'amount' => $request->amount,
                'store_expense_type_id' => $request->store_expense_type_id,
                'incurred_by' => $incurredBy,
            ]);

            if ($request->hasFile('attachment')) {
                $attachment = $this->storeFile($request->file('attachment'), 'store_expense_file');
                $expense->primaryImage()->create([...$attachment, 'media_role' => 'store_expense_file']);
            }
            $expense->save();
            DB::commit();
            return redirect::back()->with('toast', [
                'type' => 'success',
                'message' => 'Expense created successfully'
            ]);
        } catch (Exception $e) {
            DB::rollback();
            dd($e);
            return Redirect::back()->with('toast', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {}

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
        $incurredBy = Auth::id();
        $expense = StoreExpense::findOrFail($id);

        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:store_expenses',
                'description' => 'nullable|string',
                'amount' => 'required|numeric',
                'attachment' => 'required|max:2048',
            ]);

            DB::beginTransaction();
            $expense->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'amount' => $request->amount,
                'store_expense_type_id' => $request->store_expense_type_id,
                'incurred_by' => $incurredBy,

            ]);

            if ($request->hasFile('attachment')) {
                $expense->primaryImage()->delete();
                $attachment = $this->storeFile($request->file('attachment'), 'store_expense_file');
                $expense->primaryImage()->create([...$attachment, 'media_role' => 'store_expense_file']);
            } else {
                $expense->primaryImage()->delete();
            }
            $expense->update();
            DB::commit();

            return redirect::back()->with('toast', [
                'type' => 'success',
                'message' => 'Expense updated successfully',
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
            $expense = StoreExpense::findOrFail($id);
            $expense->primaryImage()->delete();
            $expense->delete();

            return Redirect::back()->with('toast', [
                'type' => 'success',
                'message' => 'Expense deleted successfully',
            ]);
        } catch (Exception $e) {
            return Redirect::back()->with('toast', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
