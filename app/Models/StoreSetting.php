<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get($key, $default = null)
    {
        return optional(static::where('key', $key)->first())->value ?? $default;
    }

    public static function set($key, $value)
    {
        return static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Get current cash balance
     */
    public static function currentCashBalance(): float
    {
        return (float) \App\Models\StoreOrder::where('payment_method', 'cash')
            ->where('payment_status', 'paid')
            ->sum('paid_amount');
    }

    /**
     * Get current Bkash balance
     */
    public static function currentBkashBalance(): float
    {
        return (float) \App\Models\StoreOrder::where('payment_method', 'bkash')
            ->where('payment_status', 'paid')
            ->sum('paid_amount');
    }

    /**
     * Get total balance (Cash + Bkash)
     */
    public static function totalBalance(): float
    {
        return self::currentCashBalance() + self::currentBkashBalance();
    }

    /**
     * Get total withdrawals
     * Assumes you have a withdrawals table with columns: amount, payment_method
     */
    public static function totalWithdrawals(): float
    {
        return (float) Withdrawl::where('reference', 'store')
            ->where('status', 'approved')
            ->sum('amount');
    }

    /**
     * Get available balance after withdrawals
     */
    public static function availableBalance(): float
    {
        return self::totalBalance() - self::totalWithdrawals();
    }

    /**
     * Get due balance (if any)
     * Assuming you have a 'due' column in your orders table
     */
    public static function dueBalance(): float
    {
        return (float) \App\Models\StoreOrder::where('payment_status', 'due')
            ->sum('due_amount');
    }
}
