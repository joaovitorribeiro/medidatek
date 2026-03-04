<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'string', 'email', 'max:190'],
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:190'],
            'pain' => ['nullable', 'string', 'max:80'],
            'investment_range' => ['required', 'string', 'max:80'],
            'message' => ['nullable', 'string', 'max:5000'],
            'utm_source' => ['nullable', 'string', 'max:120'],
            'utm_medium' => ['nullable', 'string', 'max:120'],
            'utm_campaign' => ['nullable', 'string', 'max:120'],
            'utm_content' => ['nullable', 'string', 'max:120'],
            'utm_term' => ['nullable', 'string', 'max:120'],
            'landing_path' => ['nullable', 'string', 'max:255'],
            'referrer' => ['nullable', 'string', 'max:255'],
            'contact_consent' => ['required', 'boolean', 'accepted'],
        ]);

        Lead::create([
            ...$validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Recebemos seu contato. Em breve um especialista fala com você.');
    }
}
