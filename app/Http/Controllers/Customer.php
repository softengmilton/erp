<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class Customer extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:customers,email',
                'phone' => 'nullable|string|max:15',
            ]);
            // Assuming you have a Customer model
            $customer = new \App\Models\Customer();
            $customer->name = $validatedData['name'];
            $customer->email = $validatedData['email'];
            $customer->phone = $validatedData['phone'] ?? null;
            $customer->save();
            return Redirect::back()->with('toast', [
                'type' => 'success',
                'message' => 'New customer created successfully.',
            ]);
        } catch (\Exception $e) {
            return Redirect::back()->with('toast', [
                'type' => 'error',
                'message' => 'Failed to delete stock: ' . $e->getMessage(),
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
