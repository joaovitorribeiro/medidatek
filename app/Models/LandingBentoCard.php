<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingBentoCard extends Model
{
    protected $fillable = [
        'key',
        'title',
        'image_url',
        'alt',
        'sort_order',
    ];
}

