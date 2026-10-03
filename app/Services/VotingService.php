<?php

namespace App\Services;

use App\Enums\CandidateStatus;
use App\Exceptions\VotingException;
use App\Models\Ballot;
use App\Models\BallotChoice;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Participation;
use App\Models\Position;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VotingService
{
    public function __construct(private ElectionService $elections) {}

    /**
     * @param  array<int|string, int|string>  $choices
     * @return array<int, int>
     */
    public function normalizeChoices(Election $election, array $choices): array
    {
        $positions = $this->votablePositions($election);

        if ($positions->isEmpty()) {
            throw new VotingException('no_candidates');
        }

        $allowed = $positions->pluck('id')->all();
        $normalized = [];

        foreach ($choices as $positionId => $candidateId) {
            if ($candidateId === null || $candidateId === '') {
                continue;
            }

            $positionId = (int) $positionId;

            if (! in_array($positionId, $allowed, true)) {
                throw new VotingException('invalid_candidate');
            }

            $normalized[$positionId] = (int) $candidateId;
        }

        if ($normalized === []) {
            throw new VotingException('incomplete');
        }

        $candidates = Candidate::query()
            ->where('election_id', $election->id)
            ->where('status', CandidateStatus::Active)
            ->whereIn('id', array_values($normalized))
            ->get()
            ->keyBy('id');

        foreach ($normalized as $positionId => $candidateId) {
            $candidate = $candidates->get($candidateId);

            if (! $candidate || (int) $candidate->position_id !== $positionId) {
                throw new VotingException('invalid_candidate');
            }
        }

        return $normalized;
    }

    public function cast(Election $election, array $choices): Ballot
    {
        return DB::transaction(function () use ($election, $choices) {
            $election = Election::query()->whereKey($election->id)->lockForUpdate()->firstOrFail();
            $this->elections->refreshStatus($election);

            if (! $this->elections->isAcceptingVotes($election)) {
                throw new VotingException('closed');
            }

            $normalized = $this->normalizeChoices($election, $choices);
            $now = now();

            $ballot = Ballot::query()->create([
                'election_id' => $election->id,
                'receipt_code' => $this->receiptCode(),
                'cast_at' => $now,
            ]);

            foreach ($normalized as $positionId => $candidateId) {
                BallotChoice::query()->create([
                    'ballot_id' => $ballot->id,
                    'position_id' => $positionId,
                    'candidate_id' => $candidateId,
                ]);
            }

            return $ballot;
        });
    }

    /**
     * @return Collection<int, Position>
     */
    public function votablePositions(Election $election): Collection
    {
        return Position::query()
            ->where('election_id', $election->id)
            ->where('is_active', true)
            ->whereHas('candidates', function ($query) {
                $query->where('status', CandidateStatus::Active);
            })
            ->with(['candidates' => function ($query) {
                $query->where('status', CandidateStatus::Active)
                    ->orderBy('display_order')
                    ->orderBy('last_name')
                    ->orderBy('first_name');
            }])
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();
    }

    private function receiptCode(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (
            Ballot::query()->where('receipt_code', $code)->exists()
            || Participation::query()->where('receipt_code', $code)->exists()
        );

        return $code;
    }
}
