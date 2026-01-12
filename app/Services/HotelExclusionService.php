<?php

namespace App\Services;

use App\Models\Hotel;
use App\Models\HotelExclusionRule;

class HotelExclusionService
{
    /**
     * Cached rules instance to avoid repeated DB queries
     */
    protected ?HotelExclusionRule $rules = null;

    /**
     * Get exclusion rules (cached per request)
     */
    protected function rules(): ?HotelExclusionRule
    {
        return $this->rules ??= HotelExclusionRule::first();
    }

    /**
     * ✅ Evaluate DB hotel (Admin / Internal hotels)
     */
    public function evaluateHotelData(Hotel $hotel): array
    {
        // 1️⃣ Manual override always wins
        if ($hotel->relationLoaded('exclusion') && $hotel->exclusion) {
            if ($hotel->exclusion->mode === 'force_hidden') {
                return $this->result(false, ['Manually hidden'], 'manual');
            }

            if ($hotel->exclusion->mode === 'force_visible') {
                return $this->result(true, [], 'manual');
            }
        }

        // 2️⃣ Automatic rules
        $rules = $this->rules();
        if (!$rules) {
            return $this->result(true, [], 'none');
        }

        $reasons = [];

        if ($rules->exclude_inactive && $hotel->status !== 'active') {
            $reasons[] = 'Inactive / Not Bookable';
        }

        if ($rules->exclude_no_description && blank($hotel->description)) {
            $reasons[] = 'No Description';
        }

        // IMPORTANT: use media_count (avoid N+1 queries)
        if ($rules->exclude_no_images && ($hotel->media_count ?? 0) === 0) {
            $reasons[] = 'No Images';
        }

        if ($hotel->rating !== null && $hotel->rating < $rules->min_rating) {
            $reasons[] = 'Low Rating';
        }

        if (
            $hotel->total_reviews !== null &&
            $hotel->total_reviews < $rules->min_reviews
        ) {
            $reasons[] = 'Low Reviews';
        }

        if (!empty($reasons)) {
            return $this->result(false, $reasons, 'automatic');
        }

        return $this->result(true, [], 'none');
    }

    /**
     * ✅ Evaluate API / Hotelbeds hotel (array-based)
     */
    public function evaluateFromArray(array $hotelData): array
    {
        $rules = $this->rules();
        if (!$rules) {
            return $this->result(true, [], 'none');
        }

        $reasons = [];

        if (
            $rules->exclude_inactive &&
            ($hotelData['status'] ?? 'active') !== 'active'
        ) {
            $reasons[] = 'Inactive / Not Bookable';
        }

        if (
            $rules->exclude_no_description &&
            blank($hotelData['description'] ?? null)
        ) {
            $reasons[] = 'No Description';
        }

        if (
            $rules->exclude_no_images &&
            empty($hotelData['images'] ?? [])
        ) {
            $reasons[] = 'No Images';
        }

        if (
            isset($hotelData['rating']) &&
            $hotelData['rating'] < $rules->min_rating
        ) {
            $reasons[] = 'Low Rating';
        }

        if (
            isset($hotelData['totalReviews']) &&
            $hotelData['totalReviews'] < $rules->min_reviews
        ) {
            $reasons[] = 'Low Reviews';
        }

        if (!empty($reasons)) {
            return $this->result(false, $reasons, 'automatic');
        }

        return $this->result(true, [], 'none');
    }

    /**
     * Standardized response format
     */
    protected function result(bool $visible, array $reasons, string $source): array
    {
        return [
            'visible' => $visible,
            'reasons' => $reasons,
            'source'  => $source, // manual | automatic | none
        ];
    }
}
