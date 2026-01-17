<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Services\Marketplace\MarketplaceSearchService;
use Illuminate\Http\Request;

class MarketplaceSearchController extends Controller
{
    public function show()
    {
        return view('marketplace.search');
    }

    public function search(Request $request, MarketplaceSearchService $searchService)
    {
        $data = $request->validate([
            'query' => 'required|string|max:255',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
        ]);

        $data['children'] = $data['children'] ?? 0;

        $includeDiscounted = $request->user() !== null;

        $results = $searchService->search($data, $includeDiscounted);

        return view('marketplace.results', [
            'query' => $data,
            'results' => $results,
            'includeDiscounted' => $includeDiscounted,
        ]);
    }
}
