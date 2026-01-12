<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MspRequest extends FormRequest
{
    public function authorize()
    {
        return true;
        // adjust if you have permission logic, otherwise allow
        // return $this->user() && $this->user()->can('manage-settings');
    }

    public function rules()
    {
        $rules = [
            'scope' => ['required', 'in:global,country,city'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'msp_amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
        ];

        // conditional required rules:
        if ($this->input('scope') === 'country') {
            $rules['country'][] = 'required';
        }

        if ($this->input('scope') === 'city') {
            $rules['country'][] = 'required';
            $rules['city'][] = 'required';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'country.required' => 'Country is required for country-scoped MSP.',
            'city.required' => 'City is required for city-scoped MSP.',
        ];
    }
}
