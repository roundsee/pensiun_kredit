<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoanProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'loan_number' => ['required', 'string', 'max:255', 'unique:loans,loan_number'],
            'product_id' => ['required', 'exists:products,id'],
            'nasabah_id' => ['required', 'exists:users,id'],
            'lender_id' => ['required', 'exists:lenders,id'],
            'amount_plafond' => ['required', 'numeric', 'min:0.01'],
            'interest_rate' => ['required', 'numeric', 'min:0'],
            'provision_fee' => ['nullable', 'numeric', 'min:0'],
            'admin_fee' => ['nullable', 'numeric', 'min:0'],
            'debtor_data' => ['nullable', 'array'],
            'submission_data' => ['nullable', 'array'],
            'financial_data' => ['nullable', 'array'],
        ];
    }
}
