<?php
// app/Http/Middleware/TrackUserActivity.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $userId = Auth::id();
            $cacheKey = "user_last_seen_{$userId}";

            if (!Cache::has($cacheKey)) {
                // ✅ Use Eloquent directly instead of Auth::user()->update()
                User::where('id', $userId)->update([
                    'last_seen_at'     => now(),
                    'last_activity_at' => now(),
                ]);

                Cache::put($cacheKey, true, now()->addMinutes(2));
            }
        }

        return $next($request);
    }
}