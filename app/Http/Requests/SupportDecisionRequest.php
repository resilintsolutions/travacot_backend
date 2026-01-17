<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupportDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'seller_responded' => ['required', 'boolean'],
            'buyer_responded' => ['required', 'boolean'],
            'decision' => ['required', 'string', 'in:payout_continue,payout_cancel'],
            'status' => ['required', 'string', 'in:open,solved'],
        ];
    }
}
