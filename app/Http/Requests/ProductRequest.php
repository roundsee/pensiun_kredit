<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
            'fields' => ['nullable', 'array'],
            'fields.*.field_name' => ['required_with:fields', 'string', 'max:100'],
            'fields.*.field_label' => ['required_with:fields', 'string', 'max:150'],
            'fields.*.field_type' => ['required_with:fields', 'string', 'max:50'],
            'fields.*.is_required' => ['nullable', 'boolean'],
            'fields.*.group' => ['required_with:fields', 'in:informasi_debitur,data_pengajuan'],

            'financials' => ['nullable', 'array'],
            'financials.*.item_name' => ['required_with:financials', 'string', 'max:120'],
            'financials.*.account_id' => ['required_with:financials', 'integer', 'exists:accounts,id'],
            'financials.*.calculation_type' => ['required_with:financials', 'in:percentage,fixed'],
            'financials.*.default_value' => ['required_with:financials', 'numeric', 'min:0'],
            'financials.*.transaction_type' => ['required_with:financials', 'in:debit,credit'],
            'financials.*.is_deducted_at_disbursement' => ['nullable', 'boolean'],
            'financials.*.is_included_in_simulation' => ['nullable', 'boolean'],
        ];
    }
}
