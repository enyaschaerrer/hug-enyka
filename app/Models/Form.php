<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $fillable = [
        'company_id', 'name', 'email', 'phone',
        'address', 'message', 'trophy', 'treated',
    ];

    protected $casts = [
        'trophy'  => 'boolean',
        'treated' => 'boolean',
    ];
}
