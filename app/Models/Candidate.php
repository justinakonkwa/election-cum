<?php

namespace App\Models;

use App\Enums\CandidateStatus;
use Database\Factories\CandidateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'election_id',
    'position_id',
    'first_name',
    'last_name',
    'post_name',
    'matricule',
    'photo_path',
    'biography',
    'display_order',
    'status',
])]
class Candidate extends Model
{
    /** @use HasFactory<CandidateFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'status' => CandidateStatus::class,
        ];
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function ballotChoices(): HasMany
    {
        return $this->hasMany(BallotChoice::class);
    }

    public function fullName(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->post_name,
            $this->last_name,
        ])));
    }
}
