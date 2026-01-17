<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromoTrackingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hotel_id' => ['nullable', 'integer', 'exists:hotels,id'],
            'context' => ['nullable', 'array'],
        ];
    }
}
