<?php
// app/Http/Middleware/LogUserActivity.php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Support\Facades\Request;

class LogUserActivity
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        // Log login/logout events
        if (auth()->check()) {
            $route = $request->route();
            $action = $route ? $route->getName() : null;
            
            if ($action === 'login') {
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'user_role' => auth()->user()->role,
                    'action' => 'login',
                    'description' => 'User logged in',
                    'ip_address' => Request::ip(),
                    'user_agent' => Request::userAgent(),
                ]);
            } elseif ($action === 'logout') {
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'user_role' => auth()->user()->role,
                    'action' => 'logout',
                    'description' => 'User logged out',
                    'ip_address' => Request::ip(),
                    'user_agent' => Request::userAgent(),
                ]);
            }
        }
        
        return $response;
    }
}