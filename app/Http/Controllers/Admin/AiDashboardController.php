<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiAuditLog;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AiDashboardController extends Controller
{
    public function index()
    {
        $lastLogs = AiAuditLog::query()
            ->latest()
            ->limit(50)
            ->get([
                'id',
                'created_at',
                'endpoint',
                'status',
                'model',
                'total_tokens',
                'latency_ms',
            ]);

        $byStatus = AiAuditLog::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->orderByDesc('count')
            ->get();

        $byEndpoint = AiAuditLog::query()
            ->select('endpoint', DB::raw('count(*) as count'))
            ->groupBy('endpoint')
            ->orderByDesc('count')
            ->get();

        return Inertia::render('Admin/Ai', [
            'lastLogs' => $lastLogs,
            'byStatus' => $byStatus,
            'byEndpoint' => $byEndpoint,
        ]);
    }
}
