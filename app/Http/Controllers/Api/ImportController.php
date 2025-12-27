<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ImportHotelsFromHotelbeds;
use App\Models\ImportJob;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function import(Request $req)
    {
        $payload = $req->all();
        $job = ImportJob::create(['source' => 'hotelbeds', 'status' => 'queued', 'payload' => $payload]);
        ImportHotelsFromHotelbeds::dispatch($job);
        return response()->json(['success' => true, 'jobId' => $job->id]);
    }

    public function jobStatus(ImportJob $importJob)
    {
        return response()->json(['success' => true, 'data' => $importJob]);
    }
}
