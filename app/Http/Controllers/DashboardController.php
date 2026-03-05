<?php

namespace App\Http\Controllers;

use App\Models\AiAuditLog;
use App\Models\Lead;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $metrics = [
            'projetos_total' => Project::query()->count(),
            'projetos_publicados' => Project::query()->where('is_published', true)->count(),
            'leads_total' => Lead::query()->count(),
            'leads_30d' => Lead::query()->where('created_at', '>=', now()->subDays(30))->count(),
            'ia_requisicoes_7d' => AiAuditLog::query()->where('created_at', '>=', now()->subDays(7))->count(),
            'ia_custo_30d_usd' => (string) (AiAuditLog::query()
                ->where('created_at', '>=', now()->subDays(30))
                ->sum('cost_usd') ?? 0),
        ];

        $recent = [
            'projetos' => Project::query()
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get(['id', 'name', 'url', 'is_published', 'updated_at'])
                ->map(fn (Project $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'url' => $p->url,
                    'is_published' => (bool) $p->is_published,
                    'updated_at' => $p->updated_at?->toISOString(),
                ])
                ->values(),
            'leads' => Lead::query()
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(['id', 'name', 'email', 'company', 'created_at'])
                ->map(fn (Lead $l) => [
                    'id' => $l->id,
                    'name' => $l->name,
                    'email' => $l->email,
                    'company' => $l->company,
                    'created_at' => $l->created_at?->toISOString(),
                ])
                ->values(),
        ];

        return Inertia::render('Dashboard', [
            'isAdmin' => (bool) ($user?->is_admin ?? false),
            'metrics' => $metrics,
            'recent' => $recent,
        ]);
    }
}

