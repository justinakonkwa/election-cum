<?php

namespace App\Services;

use App\Enums\CandidateStatus;
use App\Exceptions\VotingException;
use App\Enums\VoterStatus;
use App\Models\Ballot;
use App\Models\BallotChoice;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Participation;
use App\Models\Position;
use App\Models\Voter;
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

        $normalized = [];

        foreach ($choices as $positionId => $candidateId) {
            $normalized[(int) $positionId] = (int) $candidateId;
        }

        $expected = $positions->pluck('id')->sort()->values()->all();
        $given = collect(array_keys($normalized))->sort()->values()->all();

        if ($expected !== $given) {
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

    public function cast(Election $election, Voter $voter, array $choices): Ballot
    {
        return DB::transaction(function () use ($election, $voter, $choices) {
            $election = Election::query()->whereKey($election->id)->lockForUpdate()->firstOrFail();
            $voter = Voter::query()->whereKey($voter->id)->lockForUpdate()->firstOrFail();
            $this->elections->refreshStatus($election);

            if (! $this->elections->isAcceptingVotes($election)) {
                throw new VotingException('closed');
            }

            if ((int) $voter->election_id !== (int) $election->id || $voter->status !== VoterStatus::Eligible) {
                throw new VotingException('ineligible');
            }

            if ($voter->has_voted || $voter->participation()->exists()) {
                throw new VotingException('already_voted');
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

            Participation::query()->create([
                'election_id' => $election->id,
                'voter_id' => $voter->id,
                'receipt_code' => $this->receiptCode(),
                'voted_at' => $now,
            ]);

            $voter->forceFill([
                'has_voted' => true,
                'voted_at' => $now,
            ])->save();

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
