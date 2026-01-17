<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdentityVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->identity_verified_at) {
            return redirect()
                ->route('marketplace.verification.show')
                ->with('error', 'Identity verification is required to resell bookings.');
        }

        return $next($request);
    }
}
