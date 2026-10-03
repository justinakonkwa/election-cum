<?php

namespace App\Services;

use App\Models\BallotChoice;
use App\Models\Election;
use Illuminate\Support\Collection;

class ResultService
{
    /**
     * @return Collection<int, int>
     */
    public function votesByCandidate(Election $election): Collection
    {
        return BallotChoice::query()
            ->selectRaw('candidate_id, count(*) as votes')
            ->whereHas('ballot', fn ($query) => $query->where('election_id', $election->id))
            ->groupBy('candidate_id')
            ->pluck('votes', 'candidate_id')
            ->map(fn ($votes) => (int) $votes);
    }
}
