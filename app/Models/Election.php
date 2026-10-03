<?php

namespace App\Models;

use App\Enums\ElectionStatus;
use Database\Factories\ElectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'slug',
    'name',
    'description',
    'institution',
    'organization',
    'start_at',
    'end_at',
    'timezone',
    'status',
    'voting_type',
    'results_visible_before_close',
    'is_public_default',
    'created_by',
    'closed_by',
    'closed_at',
    'published_at',
])]
class Election extends Model
{
    public const TYPE_CANDIDATES = 'candidates';

    /** @use HasFactory<ElectionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'closed_at' => 'datetime',
            'published_at' => 'datetime',
            'status' => ElectionStatus::class,
            'results_visible_before_close' => 'boolean',
            'is_public_default' => 'boolean',
        ];
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function voters(): HasMany
    {
        return $this->hasMany(Voter::class);
    }

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    public function ballots(): HasMany
    {
        return $this->hasMany(Ballot::class);
    }

    public function resultsAreVisible(): bool
    {
        if ($this->results_visible_before_close) {
            return true;
        }

        return in_array($this->status, [ElectionStatus::Closed, ElectionStatus::Published], true);
    }
}
