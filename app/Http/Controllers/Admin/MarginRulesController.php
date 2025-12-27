<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarginRulesSetting;
use App\Models\MarginRuleParameters;
use Illuminate\Http\Request;
use App\Services\HotelbedsService;

class MarginRulesController extends Controller
{
    public function index()
    {
        $hb = new HotelbedsService();

        return view('admin.pricing.margin-rules.index', [
            'global'       => MarginRulesSetting::global()->first(),
            'countryRules' => MarginRulesSetting::where('scope', 'country')->orderBy('country')->get(),
            'cityRules'    => MarginRulesSetting::where('scope', 'city')->orderBy('country')->orderBy('city')->get(),

            'countries'    => $hb->getStaticCountries(),
            'destinations' => $hb->getStaticDestinations(),

            'parameters'   => MarginRuleParameters::first() ?? new MarginRuleParameters(),
        ]);
    }

    /* ---------------- GLOBAL ---------------- */
    public function updateGlobal(Request $request)
    {
        $data = $request->validate([
            'default_margin_percent' => 'required|numeric',
            'min_margin_percent'     => 'required|numeric',
            'max_margin_percent'     => 'required|numeric',
        ]);

        $rule = MarginRulesSetting::firstOrCreate(['scope' => 'global']);
        $rule->update($data);

        return back()->with('success', 'Global margins updated.');
    }

    /* ---------------- COUNTRY ---------------- */
    public function storeCountry(Request $request)
    {
        $data = $request->validate([
            'country'                => 'required|string',
            'default_margin_percent' => 'required|numeric',
            'min_margin_percent'     => 'nullable|numeric',
            'max_margin_percent'     => 'nullable|numeric',
        ]);

        MarginRulesSetting::create(array_merge($data, [
            'scope' => 'country'
        ]));

        return back()->with('success', 'Country rule added.');
    }

    public function updateCountry(Request $request, MarginRulesSetting $rule)
    {
        $data = $request->validate([
            'country'                => 'required|string',
            'default_margin_percent' => 'required|numeric',
        ]);

        $rule->update($data);

        return back()->with('success', 'Country rule updated.');
    }

    /* ---------------- CITY ---------------- */
    public function storeCity(Request $request)
    {
        $data = $request->validate([
            'city'                   => 'required|string',
            'country'                => 'required|string',
            'default_margin_percent' => 'required|numeric',
        ]);

        MarginRulesSetting::create(array_merge($data, [
            'scope' => 'city'
        ]));

        return back()->with('success', 'City rule added.');
    }

    public function updateCity(Request $request, MarginRulesSetting $rule)
    {
        $data = $request->validate([
            'city'                   => 'required|string',
            'country'                => 'required|string',
            'default_margin_percent' => 'required|numeric',
        ]);

        $rule->update($data);

        return back()->with('success', 'City rule updated.');
    }

    /* ---------------- PARAMETERS A/B/C ---------------- */
    public function updateParameters(Request $request)
    {
        $data = $request->validate([
            'demand_high_threshold_rooms'          => 'required|numeric',
            'demand_high_margin_increase_percent'  => 'required|numeric',
            'demand_low_margin_decrease_percent'   => 'required|numeric',

            'competitor_price_diff_threshold_percent' => 'required|numeric',
            'competitor_margin_decrease_percent'      => 'required|numeric',

            'conversion_threshold_percent'         => 'required|numeric',
            'conversion_margin_decrease_percent'   => 'required|numeric',
        ]);

        $params = MarginRuleParameters::firstOrCreate(['id' => 1]);

        $data['enable_demand_rule']     = $request->has('enable_demand_rule');
        $data['enable_competitor_rule'] = $request->has('enable_competitor_rule');
        $data['enable_conversion_rule'] = $request->has('enable_conversion_rule');
        
        $params->update($data);

        return back()->with('success', 'Rule A/B/C parameters updated.');
    }

    /* ---------------- DELETE ---------------- */
    public function destroy(MarginRulesSetting $rule)
    {
        if ($rule->scope === 'global') {
            return back()->with('error', 'Global rule cannot be deleted.');
        }

        $rule->delete();

        return back()->with('success', 'Rule deleted.');
    }

    /* ---------------- JSON FOR EDIT MODAL ---------------- */
    public function showJson(MarginRulesSetting $rule)
    {
        return response()->json($rule);
    }
}
