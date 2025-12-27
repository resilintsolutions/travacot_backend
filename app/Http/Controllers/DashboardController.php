<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {   
        // return "i am here";
        // // Option A: render one view but show sections by permission in Blade
        return view('dashboard');

        // Option B: redirect based on role
        // if (Auth::user()->hasRole('admin')) return view('dashboards.admin');
        // if (Auth::user()->hasRole('manager')) return view('dashboards.manager');
        // return view('dashboards.user');
    }
//     public function import(Request $req)
// {
//     $job = ImportJob::create(['source'=>'hotelbeds','status'=>'queued','payload'=>$req->all()]);
//     ImportHotelsFromHotelbeds::dispatch($job);
//     return response()->json(['success'=>true,'jobId'=>$job->id]);
// }

}


