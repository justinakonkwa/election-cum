<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ElectionStatus;
use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Services\ElectionService;
use App\Services\ResultService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(ElectionService $elections, ResultService $results): View
    {
        $election = $elections->publicElection();

        if ($election) {
            $elections->refreshStatus($election);
        }

        $votes = $election?->ballots()->count() ?? 0;
        $tallies = ($election && $election->resultsAreVisible())
            ? $results->votesByCandidate($election)
            : null;

        $candidates = $election
            ? Candidate::query()->with('position')->where('election_id', $election->id)->orderBy('position_id')->orderBy('last_name')->get()
            : collect();

        return view('admin.dashboard', [
            'election' => $election,
            'votes' => $votes,
            'ballots' => $votes,
            'tallies' => $tallies,
            'candidates' => $candidates,
        ]);
    }

    public function updateStatus(Request $request, ElectionService $elections): RedirectResponse
    {
        $election = $elections->publicElection();

        if (! $election) {
            return back()->with('error', 'Aucune élection n\'est disponible.');
        }

        $status = ElectionStatus::from($request->validate([
            'status' => ['required', Rule::enum(ElectionStatus::class)],
        ])['status']);

        $election->status = $status;

        if ($status === ElectionStatus::Closed) {
            $election->closed_at = now();
            $election->closed_by = $request->user()->id;
        }

        $election->save();

        return back();
    }
}
