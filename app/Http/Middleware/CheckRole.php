<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role;
        
        // If no role is assigned, deny access
        if (!$userRole) {
            abort(403, 'No role assigned. Please contact administrator.');
        }
        
        // Check if user's role is in the allowed roles
        if (in_array($userRole, $roles)) {
            return $next($request);
        }
        
        abort(403, 'You do not have permission to access this page.');
    }
}