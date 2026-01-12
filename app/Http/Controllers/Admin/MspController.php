<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MspRequest;
use App\Models\MspSetting;
use App\Services\HotelbedsService;

class MspController extends Controller
{
    public function index()
    {
        $hb = new HotelbedsService();

        return view('admin.pricing.msp.index', [
            'global'    => MspSetting::global()->first(),
            'countries' => MspSetting::where('scope','country')->orderBy('country')->get(),
            'cities'    => MspSetting::where('scope','city')->orderBy('country')->orderBy('city')->get(),
            'uiCountries'=> $hb->getStaticCountries(),
            'uiDestinations' => $hb->getStaticDestinations()
        ]);
    }

    public function store(MspRequest $request)
    {
        $data = $request->validated();

        MspSetting::updateOrCreate(
            ['scope' => $data['scope'], 'country' => $data['country'] ?? null, 'city' => $data['city'] ?? null],
            ['msp_amount' => $data['msp_amount'], 'currency' => $data['currency']]
        );

        return back()->with('success', 'MSP saved.');
    }

    public function edit(MspSetting $msp)
    {
        return view('admin.pricing.msp.form', ['msp' => $msp, 'mode' => 'edit']);
    }

    public function update(MspRequest $request, MspSetting $msp)
    {
        $msp->update($request->validated());
        return back()->with('success', 'MSP updated.');
    }

    public function destroy(MspSetting $msp)
    {
        if ($msp->scope === 'global') {
            return back()->with('error', 'Cannot delete global MSP.');
        }

        $msp->delete();
        return back()->with('success', 'MSP deleted.');
    }

    /**
     * JSON endpoint for AJAX edit modal.
     */
    public function showJson(MspSetting $msp)
    {
        return response()->json($msp);
    }

}
