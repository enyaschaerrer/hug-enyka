<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'company_id',
    'start',
    'end',
    'access_token',
    'linkOneDoc',
])]
class Collection extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'start' => 'datetime',
            'end' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'collections_users')->withTimestamps();
    }

    public function isActive(?CarbonInterface $now = null): bool
    {
        $now ??= now();

        return $this->end !== null && $this->end->greaterThanOrEqualTo($now);
    }
}
