<?php

namespace App\Http\Controllers\Store\Order;

use App\Http\Controllers\Controller;
use App\Models\StoreOrder;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Http\Request;
use Inertia\Inertia;
use DB;
use Illuminate\Support\Facades\Redirect;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $orders = StoreOrder::query()
            ->with(['customer', 'storeOrderItems'])
            ->when($request->search, function ($query, $search) {
                $query->where('order_number', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('payment_status', $status);
            })
            ->when($request->payment_method, function ($query, $method) {
                $query->where('payment_method', $method);
            })
            ->when($request->customer_type, function ($query, $type) {
                $query->where('customer_type', $type);
            })
            ->when($request->date_range, function ($query, $range) {
                switch ($range) {
                    case 'today':
                        $query->whereDate('created_at', today());
                        break;
                    case 'this_week':
                        $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                        break;
                    case 'this_month':
                        $query->whereMonth('created_at', now()->month);
                        break;
                    case 'last_3_months':
                        $query->where('created_at', '>=', now()->subMonths(3));
                        break;
                    case 'last_6_months':
                        $query->where('created_at', '>=', now()->subMonths(6));
                        break;
                    case 'this_year':
                        $query->whereYear('created_at', now()->year);
                        break;
                }
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('store/order/Order', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'status', 'payment_method', 'customer_type', 'date_range']),
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
    public function show(StoreOrder $order)
    {
        $order->load([
            'customer',
            'storeOrderItems.storeProduct',
            'storeOrderItems.storeStock'
        ]);

        return Inertia::render('store/order/Show', [
            'order' => $order,
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
public function update(Request $request, $id)
{
    try {
        $order = StoreOrder::findOrFail($id);

        $request->validate([
            'due_amount' => 'required|numeric',
        ]);

        if ($order->due_amount >= $request->due_amount) {
            if ($order->due_amount == $request->due_amount) {
                $order->update([
                    'paid_amount' => $order->paid_amount + $request->due_amount,
                    'due_amount' => 0,
                    'payment_status' => 'paid',
                ]);

                return redirect()->back()->with([
                    'toast' => [
                        'type' => 'success',
                        'message' => 'Payment updated successfully!'
                    ]
                ]);
            } else {
                $new_due_amount = $order->due_amount - $request->due_amount;

                $order->update([
                    'paid_amount' => $order->paid_amount + $request->due_amount,
                    'due_amount' => $new_due_amount,
                    'payment_status' => 'partial',
                ]);

                return redirect()->back()->with([
                    'toast' => [
                        'type' => 'success',
                        'message' => 'Partial payment updated successfully!'
                    ]
                ]);
            }
        } else {
            return redirect()->back()->with([
                'toast' => [
                    'type' => 'error',
                    'message' => 'Payment amount cannot be greater than due amount.'
                ]
            ]);
        }
    } catch (\Exception $e) {
        return redirect()->back()->with([
            'toast' => [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]
        ]);
    }
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Delete related stock movements first
        DB::table('store_stock_movements')
            ->where('source_data->order_id', $id)
            ->delete();

     
        // Delete the order itself
        DB::table('store_orders')
            ->where('id', $id)
            ->delete();

        return Redirect::back()->with('toast', [
            'type' => 'success',
            'message' => 'Order deleted successfully.',
        ]);
    }
}