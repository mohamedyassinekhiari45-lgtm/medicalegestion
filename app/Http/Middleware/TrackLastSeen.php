<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class TrackLastSeen
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            if (!$user->last_seen_at || $user->last_seen_at->lt(now()->subMinute())) {
                $user->update(['last_seen_at' => now()]);
            }
        }
        return $next($request);
    }
}
