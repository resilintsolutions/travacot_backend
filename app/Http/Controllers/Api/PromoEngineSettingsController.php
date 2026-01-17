<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PromoEngineSettingsRequest;
use App\Models\PromoEngineSetting;
use App\Services\PromoEngine\PromoEngineConfig;

class PromoEngineSettingsController extends Controller
{
    public function show(PromoEngineConfig $config)
    {
        return response()->json([
            'data' => $config->get(),
        ]);
    }

    public function update(PromoEngineSettingsRequest $request)
    {
        $settings = PromoEngineSetting::firstOrCreate([]);
        $settings->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }
}
