<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DisburseLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cash_transferred' => ['required', 'numeric', 'min:0'],
            'funded_by_lender' => ['nullable', 'numeric', 'min:0'],
            'provision_income' => ['nullable', 'numeric', 'min:0'],
            'admin_income' => ['nullable', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:100'],
            'transaction_date' => ['nullable', 'date'],
        ];
    }
}
