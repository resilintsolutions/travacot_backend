<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hotel;

class InventoryContentHealthController extends Controller
{
        public function index(Request $request)
    {
        $search = $request->get('search', '');
        $filter = $request->get('filter', 'all'); // all | issues | critical | warning | healthy

        $query = Hotel::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        $hotels = $query->orderBy('name')->limit(200)->get();

        $healthItems = [];
        $summary = [
            'missing_photos'       => 0,
            'low_quality_photos'   => 0, // placeholder if you add it later
            'missing_descriptions' => 0,
            'missing_amenities'    => 0, // placeholder for future
            'mapping_issues'       => 0,
            'outdated_content'     => 0, // placeholder
            'total_issues'         => 0,
        ];

        foreach ($hotels as $hotel) {
            $issues = [];

            // Reuse your existing rules:
            if (empty($hotel->meta) || !data_get($hotel->meta, 'images')) {
                $issues[] = 'missing_photos';  // your old 'no_images'
                $summary['missing_photos']++;
            }

            if (empty($hotel->description)) {
                $issues[] = 'missing_descriptions';
                $summary['missing_descriptions']++;
            }

            if (empty($hotel->meta)) {
                $issues[] = 'mapping_issues'; // your old 'missing_meta'
                $summary['mapping_issues']++;
            }

            // total issues for hotel
            $issueCount = count($issues);
            $summary['total_issues'] += $issueCount;

            // simple score: 100% if no issues, 70% if 1, 40% if 2+, adjust as you like
            if ($issueCount === 0) {
                $score = 100;
                $severity = 'healthy';
            } elseif ($issueCount === 1) {
                $score = 70;
                $severity = 'warning';
            } else {
                $score = 40;
                $severity = 'critical';
            }

            $healthItems[] = [
                'hotel'      => $hotel,
                'issues'     => $issues,
                'score'      => $score,
                'severity'   => $severity,
                // derived flags for UI columns
                'photos'     => in_array('missing_photos', $issues) ? 'missing' : 'ok',
                'description'=> in_array('missing_descriptions', $issues) ? 'missing' : 'ok',
                'amenities'  => 'ok', // you can wire this later from real data
                'mapping'    => in_array('mapping_issues', $issues) ? 'wrong' : 'ok',
            ];
        }

        // Filter by severity/tab
        $filtered = collect($healthItems)->filter(function ($item) use ($filter) {
            if ($filter === 'all') return true;
            if ($filter === 'issues') return count($item['issues']) > 0;
            if (in_array($filter, ['critical','warning','healthy'])) {
                return $item['severity'] === $filter;
            }
            return true;
        })->values();

        return view('admin.inventory.content_health', [
            'summary'     => $summary,
            'healthItems' => $filtered,
            'filter'      => $filter,
            'search'      => $search,
        ]);
    }

}
