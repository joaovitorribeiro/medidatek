<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Inertia\Inertia;

class LeadController extends Controller
{
    public function index()
    {
        $leads = Lead::query()
            ->orderByDesc('id')
            ->simplePaginate(30, [
                'id',
                'name',
                'email',
                'whatsapp',
                'company',
                'pain',
                'investment_range',
                'message',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_content',
                'utm_term',
                'landing_path',
                'referrer',
                'contact_consent',
                'ip_address',
                'user_agent',
                'created_at',
            ])
            ->withQueryString();

        return Inertia::render('Admin/Leads/Index', [
            'leads' => $leads,
        ]);
    }
}

