<?php

namespace App\Services;

use App\Enums\VoterStatus;
use App\Exceptions\VotingException;
use App\Models\Election;
use App\Models\Voter;

class VoterService
{
    public function findEligible(Election $election, string $matricule): Voter
    {
        $normalized = Voter::normalizeMatricule($matricule);

        if ($normalized === '') {
            throw new VotingException('unknown');
        }

        $voter = Voter::query()
            ->where('election_id', $election->id)
            ->where('matricule', $normalized)
            ->first();

        if (! $voter) {
            throw new VotingException('unknown');
        }

        if ($voter->status !== VoterStatus::Eligible) {
            throw new VotingException('ineligible');
        }

        if ($voter->has_voted) {
            throw new VotingException('already_voted');
        }

        return $voter;
    }
}
