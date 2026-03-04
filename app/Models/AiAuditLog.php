<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiAuditLog extends Model
{
    protected $fillable = [
        'endpoint',
        'session_id',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'latency_ms',
        'status',
        'error_message',
        'cost_usd',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'cost_usd' => 'decimal:6',
    ];
}
