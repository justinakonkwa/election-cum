<?php

namespace App\Http\Controllers;

use App\Enums\CandidateStatus;
use App\Enums\VoterStatus;
use App\Models\Position;
use App\Services\ElectionService;
use App\Services\ResultService;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function show(ElectionService $elections, ResultService $results): View
    {
        $election = $elections->publicElection();

        if ($election) {
            $elections->refreshStatus($election);
        }

        $positions = $election
            ? Position::query()
                ->where('election_id', $election->id)
                ->where('is_active', true)
                ->orderBy('display_order')
                ->with(['candidates' => function ($query) {
                    $query->where('status', CandidateStatus::Active)->orderBy('display_order')->orderBy('last_name');
                }])
                ->get()
            : collect();

        $ballots = $election?->ballots()->count() ?? 0;
        $registered = $election
            ? $election->voters()->where('status', VoterStatus::Eligible)->count()
            : 0;
        $voted = $election
            ? $election->voters()->where('has_voted', true)->count()
            : 0;
        $visible = $election?->resultsAreVisible() ?? false;

        return view('election.report', [
            'election' => $election,
            'positions' => $positions,
            'ballots' => $ballots,
            'registered' => $registered,
            'voted' => $voted,
            'visible' => $visible,
            'tallies' => ($election && $visible) ? $results->votesByCandidate($election) : null,
        ]);
    }
}
