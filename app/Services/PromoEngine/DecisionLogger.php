<?php

namespace App\Services\PromoEngine;

use App\Models\PromoDecision;

class DecisionLogger
{
    public function log(array $data): PromoDecision
    {
        return PromoDecision::create([
            'hotel_id' => $data['hotel_id'] ?? null,
            'mode' => $data['mode'] ?? null,
            'discount_percent' => $data['discount_percent'] ?? null,
            'original_margin' => $data['original_margin'] ?? null,
            'final_margin' => $data['final_margin'] ?? null,
            'status' => $data['status'] ?? 'none',
            'reason' => $data['reason'] ?? null,
            'valid_until' => $data['valid_until'] ?? null,
            'context' => $data['context'] ?? null,
        ]);
    }
}
