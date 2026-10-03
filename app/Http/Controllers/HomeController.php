<?php

namespace App\Http\Controllers;

use App\Enums\CandidateStatus;
use App\Models\Candidate;
use App\Models\Position;
use App\Services\ElectionService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(ElectionService $elections): View
    {
        $election = $elections->publicElection();

        if ($election) {
            $elections->refreshStatus($election);
        }

        return view('election.home', [
            'election' => $election,
            'phase' => $election ? $elections->phase($election) : null,
            'remaining' => $election ? $elections->formatRemaining($election) : null,
            'target' => $election ? $elections->countdownTarget($election) : null,
        ]);
    }

    public function status(ElectionService $elections): View
    {
        $election = $elections->publicElection();

        if ($election) {
            $elections->refreshStatus($election);
        }

        return view('election.status', [
            'election' => $election,
            'positions' => $election
                ? Position::query()->where('election_id', $election->id)->where('is_active', true)->count()
                : 0,
            'candidates' => $election
                ? Candidate::query()->where('election_id', $election->id)->where('status', CandidateStatus::Active)->count()
                : 0,
        ]);
    }
}
