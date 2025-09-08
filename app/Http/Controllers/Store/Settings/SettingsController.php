<?php

namespace App\Http\Controllers\Store\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\StoreSetting;
use App\Models\Withdrawl;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class SettingsController extends Controller
{
    /**
     * Display business settings and financial balances.
     */
    public function index()
    {
        // Get settings as key => value
        $settings = StoreSetting::pluck('value', 'key')->toArray();

        if (!empty($settings['logo'])) {
            // If you store in storage/app/public
            $settings['logo_url'] = asset('storage/' . $settings['logo']);
        } else {
            $settings['logo_url'] = null;
        }
        // Cash/Bkash balances & withdrawals (from StoreSetting model methods)
        $financials = [
            'currentCash'        => StoreSetting::currentCashBalance(),
            'bkashBalance'       => StoreSetting::currentBkashBalance(),
            'totalBalance'       => StoreSetting::totalBalance(),
            'totalWithdrawn'     => StoreSetting::totalWithdrawals(),
            'availableBalance'   => StoreSetting::availableBalance(),
            'dueBalance'         => StoreSetting::dueBalance(),
        ];
        $withdrawals = Withdrawl::where('reference', 'store')
            ->latest()
            ->take(10) // only last 10
            ->get();
        return Inertia::render('store/settings/Settings', [
            'settings'   => $settings,
            'financials' => $financials,
            'withdrawals' => $withdrawals,
        ]);
    }

    /**
     * Store or update settings.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validate([
                'business_title'      => 'nullable|string|max:255',
                'business_email'      => 'nullable|email',
                'phone'               => 'nullable|string|max:20',
                'address'             => 'nullable|string',
                'description'         => 'nullable|string',
                'currency'            => 'nullable|string|max:3',
                'opening_time'        => 'nullable',
                'closing_time'        => 'nullable',
                'invoice_footer_text' => 'nullable|string',
                'logo'                => 'nullable|image|mimes:png|max:2048',
            ]);

            foreach ($data as $key => $value) {
                if ($key === 'logo' && $request->hasFile('logo')) {
                    $path = $request->file('logo')->store('logos', 'public');
                    StoreSetting::set('logo', $path);
                } else {
                    StoreSetting::set($key, $value);
                }
            }

            DB::commit();

            return Redirect::back()->with('toast', [
                'type' => 'success',
                'message' => 'Settings saved successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return Redirect::back()->with('toast', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function withdrawals(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', 'in:cash,bkash,bank'],
            'account' => ['nullable', 'string', 'max:255'],
        ]);

        // Check available balance
        $availableBalance = \App\Models\StoreSetting::availableBalance();
        if ($data['amount'] > $availableBalance) {
            return back()->withErrors(['amount' => 'Withdrawal amount exceeds available balance.']);
        }

        Withdrawl::create([
            'user_id'  => Auth::id(),
            'amount'   => $data['amount'],
            'method'   => $data['method'],
            'reference' => 'store',
            'status'   => 'approved',
            'remarks'  => $data['account'] ?? null,
        ]);

        return Redirect::back()->with('toast', [
            'type' => 'success',
            'message' => 'Withdrawal request submitted successfully!',
        ]);
    }
}
