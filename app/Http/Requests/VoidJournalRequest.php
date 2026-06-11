<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoidJournalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference' => ['required', 'string', 'max:100'],
            'reason' => ['required', 'string', 'max:255'],
            'reversal_reference' => ['nullable', 'string', 'max:100'],
            'transaction_date' => ['nullable', 'date'],
        ];
    }
}
