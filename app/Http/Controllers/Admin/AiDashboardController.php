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
        $inputUsdPerToken = ((float) config('ai.pricing.input_usd_per_million', 0.15)) / 1_000_000;
        $outputUsdPerToken = ((float) config('ai.pricing.output_usd_per_million', 0.60)) / 1_000_000;
        $usdBrl = (float) config('ai.usd_brl', 5.00);

        $lastLogs = AiAuditLog::query()
            ->latest()
            ->limit(50)
            ->get([
                'id',
                'created_at',
                'endpoint',
                'status',
                'model',
                'prompt_tokens',
                'completion_tokens',
                'total_tokens',
                'latency_ms',
                'cost_usd',
            ]);

        $lastLogs = $lastLogs->map(function (AiAuditLog $log) use ($inputUsdPerToken, $outputUsdPerToken, $usdBrl) {
            $prompt = (int) ($log->prompt_tokens ?? 0);
            $completion = (int) ($log->completion_tokens ?? 0);
            $costUsd = $log->cost_usd !== null
                ? (float) $log->cost_usd
                : (($prompt * $inputUsdPerToken) + ($completion * $outputUsdPerToken));

            return [
                'id' => $log->id,
                'created_at' => $log->created_at?->toISOString(),
                'endpoint' => $log->endpoint,
                'status' => $log->status,
                'model' => $log->model,
                'prompt_tokens' => $log->prompt_tokens,
                'completion_tokens' => $log->completion_tokens,
                'total_tokens' => $log->total_tokens,
                'latency_ms' => $log->latency_ms,
                'cost_brl' => $costUsd > 0 ? round($costUsd * $usdBrl, 4) : null,
            ];
        })->values();

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
            'usd_brl' => $usdBrl,
        ]);
    }
}
