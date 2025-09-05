<?php

namespace App\Http\Controllers\Store\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\redirect;
use DB;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $settings = StoreSetting::pluck('value', 'key')->toArray();

        return Inertia::render('store/settings/Settings', [
            'settings' => $settings
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
        DB::beginTransaction(); 
        try{
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
                    $path = $request->file('logo')->store('logos', 'public'); // store logo
                    StoreSetting::set('logo', $path); // save path as setting
                } else {
                    StoreSetting::set($key, $value);
                }
            }

            DB::commit(); 

            return Redirect::back()->with('toast', [
                'type' => 'success',
                'message' => 'Settings saved successfully!'
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
