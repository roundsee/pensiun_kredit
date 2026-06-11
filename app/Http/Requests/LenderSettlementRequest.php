<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LenderSettlementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'principal_settle' => ['required', 'numeric', 'min:0'],
            'interest_settle' => ['required', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:100'],
            'transaction_date' => ['nullable', 'date'],
        ];
    }
}
