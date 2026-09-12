<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('user')->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->string('action'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->string('user_id'));
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->string('subject_type'));
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->string('subject_id'));
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('admin.audit_logs.index', compact('logs'));
    }
}
