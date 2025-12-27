<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MarginRulesRequest;
use App\Models\MarginRulesSetting;

class MarginRulesController extends Controller
{
    public function index()
    {
        $global = MarginRulesSetting::global()->first();
        $countryRules = MarginRulesSetting::where('scope','country')->orderBy('country')->get();
        $cityRules = MarginRulesSetting::where('scope','city')->orderBy('country')->orderBy('city')->get();

        return view('admin.pricing.margin-rules.index', compact('global','countryRules','cityRules'));
    }

    public function create()
    {
        return view('admin.pricing.margin-rules.form', ['rule' => new MarginRulesSetting(), 'mode' => 'create']);
    }

    public function store(MarginRulesRequest $request)
    {
        $data = $request->validated();

        MarginRulesSetting::updateOrCreate(
            ['scope' => $data['scope'], 'country' => $data['country'] ?? null, 'city' => $data['city'] ?? null],
            collect($data)->only([
                'default_margin_percent','min_margin_percent','max_margin_percent',
                'demand_high_threshold_rooms','demand_high_margin_increase_percent','demand_low_margin_decrease_percent',
                'competitor_price_diff_threshold_percent','competitor_margin_decrease_percent',
                'conversion_threshold_percent','conversion_margin_decrease_percent'
            ])->toArray()
        );

        return redirect()->route('admin.margin-rules.index')->with('success', 'Margin rule saved.');
    }

    public function edit(MarginRulesSetting $rule)
    {
        return view('admin.pricing.margin-rules.form', ['rule' => $rule, 'mode' => 'edit']);
    }

    public function update(MarginRulesRequest $request, MarginRulesSetting $rule)
    {
        $data = $request->validated();
        $rule->update($data);

        return redirect()->route('admin.margin-rules.index')->with('success', 'Margin rule updated.');
    }

    public function destroy(MarginRulesSetting $rule)
    {
        if ($rule->scope === 'global') {
            return back()->with('error', 'Cannot delete global margin rule.');
        }

        $rule->delete();
        return redirect()->route('admin.margin-rules.index')->with('success', 'Margin rule deleted.');
    }
}
