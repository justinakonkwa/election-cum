<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['election_id', 'receipt_code', 'cast_at'])]
class Ballot extends Model
{
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'cast_at' => 'datetime',
        ];
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function choices(): HasMany
    {
        return $this->hasMany(BallotChoice::class);
    }
}
