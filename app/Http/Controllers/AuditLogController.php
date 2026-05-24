<?php
// app/Http/Controllers/AuditLogController.php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        if ($user->role === 'admin') {
            $logs = AuditLog::forAdmin()->paginate(50);
        } elseif ($user->role === 'manager') {
            $logs = AuditLog::forManager()->paginate(50);
        } else {
            abort(403, 'Unauthorized');
        }
        
        return view('audit-logs.index', compact('logs'));
    }
    
    public function show(AuditLog $auditLog)
    {
        $user = auth()->user();
        
        // Admin can see all, Manager only sees worker-related logs
        if ($user->role !== 'admin') {
            if (!in_array($auditLog->user_role, ['worker', 'head_worker'])) {
                abort(403);
            }
        }
        
        return view('audit-logs.show', compact('auditLog'));
    }
}