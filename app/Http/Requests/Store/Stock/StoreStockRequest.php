<?php

namespace App\Http\Requests\Store\Stock;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invoice_number' => 'required',
            'supplier_name' => 'required|string|max:255',
            'shipping_cost' => 'nullable|numeric|min:0',
            'other_fees' => 'nullable|numeric|min:0',
            'total_cost' => 'required|numeric|min:0',
            'note' => 'nullable',
            'document' => 'nullable|mimes:jpg,jpeg,png,pdf|max:2048',

            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:store_products,id',
            'products.*.name' => 'sometimes|string', // Added name validation
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_cost' => 'required|numeric|min:1',
            'products.*.shipping_cost_per_unit' => 'nullable|numeric|min:0',
            'products.*.other_fees_per_unit' => 'nullable|numeric|min:0',
            'products.*.unit_landed_cost' => 'nullable|numeric|min:0',
            'products.*.shipping_cost' => 'nullable|numeric|min:0',
            'products.*.other_fees' => 'nullable|numeric|min:0',
            'products.*.total_cost' => 'nullable|numeric|min:0',
            'products.*.sale_price' => 'required|numeric|min:0',
        ];
    }


    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'invoice_number.required' => 'The invoice number is required.',

            'supplier_name.required' => 'The supplier name is required.',
            'supplier_name.string' => 'The supplier name must be a string.',
            'supplier_name.max' => 'The supplier name may not be greater than 255 characters.',

            'shipping_cost.numeric' => 'The shipping cost must be a number.',
            'shipping_cost.min' => 'The shipping cost must be at least 0.',

            'other_fees.numeric' => 'Other fees must be a number.',
            'other_fees.min' => 'Other fees must be at least 0.',

            'total_cost.required' => 'The total cost is required.',
            'total_cost.numeric' => 'The total cost must be a number.',
            'total_cost.min' => 'The total cost must be at least 0.',

            'document.mimes' => 'The document must be a file of type: jpg, jpeg, png, pdf.',
            'document.max' => 'The document may not be greater than 2MB.',

            'products.required' => 'At least one product is required.',
            'products.array' => 'Products must be provided as an array.',
            'products.min' => 'At least one product is required.',

            'products.*.id.required' => 'Product ID is required.',
            'products.*.id.exists' => 'The selected product is invalid.',

            'products.*.name.string' => 'Product name must be a string.',

            'products.*.quantity.required' => 'Product quantity is required.',
            'products.*.quantity.integer' => 'Product quantity must be an integer.',
            'products.*.quantity.min' => 'Product quantity must be at least 1.',

            'products.*.unit_cost.required' => 'Unit cost is required.',
            'products.*.unit_cost.numeric' => 'Unit cost must be a number.',
            'products.*.unit_cost.min' => 'Unit cost must be at least 0.',

            'products.*.shipping_cost_per_unit.numeric' => 'Shipping cost per unit must be a number.',
            'products.*.shipping_cost_per_unit.min' => 'Shipping cost per unit must be at least 0.',

            'products.*.other_fees_per_unit.numeric' => 'Other fees per unit must be a number.',
            'products.*.other_fees_per_unit.min' => 'Other fees per unit must be at least 0.',

            'products.*.unit_landed_cost.numeric' => 'Unit landed cost must be a number.',
            'products.*.unit_landed_cost.min' => 'Unit landed cost must be at least 0.',

            'products.*.shipping_cost.numeric' => 'Shipping cost must be a number.',
            'products.*.shipping_cost.min' => 'Shipping cost must be at least 0.',

            'products.*.other_fees.numeric' => 'Other fees must be a number.',
            'products.*.other_fees.min' => 'Other fees must be at least 0.',

            'products.*.total_cost.numeric' => 'Total cost must be a number.',
            'products.*.total_cost.min' => 'Total cost must be at least 0.',

            'products.*.sale_price.required' => 'Sale price is required.',
            'products.*.sale_price.numeric' => 'Sale price must be a number.',
            'products.*.sale_price.min' => 'Sale price must be at least 0.',
        ];
    }
}
