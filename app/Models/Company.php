<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'logo',
    'short_description',
    'address',
    'email',
    'telephone',
    'employee_count',
    'slug',
    'allowed_email_domains',
    'source',
    'primaryColor',
    'secondaryColor',
    'thirdColor',
])]
class Company extends Model
{
    use HasFactory;

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }
}
