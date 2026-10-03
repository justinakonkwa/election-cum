<?php

namespace App\Models;

use App\Enums\VoterStatus;
use Database\Factories\VoterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'election_id',
    'matricule',
    'last_name',
    'post_name',
    'first_name',
    'sex',
    'faculty',
    'promotion',
    'phone',
    'email',
    'status',
    'has_voted',
    'voted_at',
])]
class Voter extends Model
{
    /** @use HasFactory<VoterFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => VoterStatus::class,
            'has_voted' => 'boolean',
            'voted_at' => 'datetime',
        ];
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function participation(): HasOne
    {
        return $this->hasOne(Participation::class);
    }

    public static function normalizeMatricule(string $value): string
    {
        $compact = preg_replace('/\s+/u', '', trim($value)) ?? '';

        return mb_strtoupper($compact, 'UTF-8');
    }
}
