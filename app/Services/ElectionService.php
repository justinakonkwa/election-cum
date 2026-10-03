<?php

namespace App\Services;

use App\Enums\ElectionStatus;
use App\Models\Election;
use Illuminate\Support\Carbon;

class ElectionService
{
    public function publicElection(): ?Election
    {
        return Election::query()
            ->where('is_public_default', true)
            ->first()
            ?? Election::query()->latest('id')->first();
    }

    public function refreshStatus(Election $election): Election
    {
        [$now, $start, $end] = $this->window($election);

        if ($election->status === ElectionStatus::Scheduled && $now->betweenIncluded($start, $end)) {
            $election->status = ElectionStatus::Open;
            $election->save();
        }

        if ($election->status === ElectionStatus::Open && $now->gt($end)) {
            $election->status = ElectionStatus::Closed;
            $election->closed_at = $now;
            $election->save();
        }

        return $election;
    }

    public function isAcceptingVotes(Election $election): bool
    {
        if ($election->status !== ElectionStatus::Open) {
            return false;
        }

        [$now, $start, $end] = $this->window($election);

        return $now->betweenIncluded($start, $end);
    }

    public function phase(Election $election): string
    {
        if ($election->status === ElectionStatus::Suspended) {
            return 'suspended';
        }

        if ($election->status === ElectionStatus::Draft) {
            return 'draft';
        }

        [$now, $start, $end] = $this->window($election);

        if ($now->lt($start) && in_array($election->status, [ElectionStatus::Scheduled, ElectionStatus::Open], true)) {
            return 'before';
        }

        if ($election->status === ElectionStatus::Open && $now->betweenIncluded($start, $end)) {
            return 'open';
        }

        return 'closed';
    }

    public function countdownTarget(Election $election): ?int
    {
        $phase = $this->phase($election);

        if ($phase === 'before') {
            return $election->start_at->getTimestamp();
        }

        if ($phase === 'open') {
            return $election->end_at->getTimestamp();
        }

        return null;
    }

    public function formatRemaining(Election $election): ?string
    {
        $target = $this->countdownTarget($election);

        if ($target === null) {
            return null;
        }

        $seconds = max(0, $target - now()->getTimestamp());

        return sprintf(
            '%02d : %02d : %02d',
            intdiv($seconds, 3600),
            intdiv($seconds, 60) % 60,
            $seconds % 60,
        );
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: Carbon}
     */
    private function window(Election $election): array
    {
        return [
            now($election->timezone),
            $election->start_at->copy()->timezone($election->timezone),
            $election->end_at->copy()->timezone($election->timezone),
        ];
    }
}
