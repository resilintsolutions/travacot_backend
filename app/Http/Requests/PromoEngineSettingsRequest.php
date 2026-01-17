<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromoEngineSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'engine_status' => ['required', 'boolean'],
            'enabled_modes' => ['required', 'array'],
            'enabled_modes.*' => ['in:light,normal,aggressive'],
            'auto_downgrade_enabled' => ['required', 'boolean'],
            'hide_promo_on_fail' => ['required', 'boolean'],
            'min_margin_eligibility' => ['required', 'numeric', 'min:0'],
            'safety_buffer' => ['required', 'numeric', 'min:0'],
            'min_profit_after_promo' => ['required', 'numeric', 'min:0'],
            'discount_strategy' => ['required', 'string', 'in:max_safe,midpoint,min'],
            'attribution_window_minutes' => ['required', 'integer', 'min:1'],
        ];
    }
}
