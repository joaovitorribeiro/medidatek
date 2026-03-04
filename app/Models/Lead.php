<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
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
    ];
}
