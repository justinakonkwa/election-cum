<?php

namespace App\Support;

use App\Models\Election;
use App\Models\Voter;

class VoterSession
{
    public const KEY = 'voter_ballot';

    public const CHOICES = 'voter_choices';

    public const RECEIPT = 'vote_receipt';

    public const MINUTES = 15;

    public static function start(Election $election, Voter $voter): void
    {
        session()->regenerate();
        session()->forget(self::CHOICES);
        session()->put(self::KEY, [
            'election_id' => $election->id,
            'voter_id' => $voter->id,
            'expires_at' => now()->addMinutes(self::MINUTES)->getTimestamp(),
        ]);
    }

    /**
     * @return array{election_id: int, voter_id: int, expires_at: int}|null
     */
    public static function get(): ?array
    {
        $payload = session(self::KEY);

        if (! is_array($payload) || ! isset($payload['election_id'], $payload['voter_id'], $payload['expires_at'])) {
            return null;
        }

        return [
            'election_id' => (int) $payload['election_id'],
            'voter_id' => (int) $payload['voter_id'],
            'expires_at' => (int) $payload['expires_at'],
        ];
    }

    public static function clearIdentity(): void
    {
        session()->forget([self::KEY, self::CHOICES]);
    }
}
