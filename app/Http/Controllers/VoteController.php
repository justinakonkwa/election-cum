<?php

namespace App\Http\Controllers;

use App\Exceptions\VotingException;
use App\Http\Requests\ReviewVoteRequest;
use App\Models\Candidate;
use App\Models\Election;
use App\Services\ElectionService;
use App\Services\VotingService;
use App\Support\VoterSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VoteController extends Controller
{
    public function ballot(ElectionService $elections, VotingService $voting): View
    {
        $election = $this->election($elections);
        $open = $election && $elections->isAcceptingVotes($election);

        return view('vote.ballot', [
            'election' => $election,
            'open' => $open,
            'positions' => $open ? $voting->votablePositions($election) : collect(),
            'selected' => session(VoterSession::CHOICES, []),
        ]);
    }

    public function review(ReviewVoteRequest $request, ElectionService $elections, VotingService $voting): RedirectResponse
    {
        $election = $this->requireOpen($elections);

        try {
            $choices = $voting->normalizeChoices($election, $request->validated('choices'));
        } catch (VotingException $exception) {
            return back()->with('error', $this->message($exception->key));
        }

        session()->put(VoterSession::CHOICES, $choices);

        return redirect()->route('vote.confirm');
    }

    public function confirm(ElectionService $elections): View|RedirectResponse
    {
        $election = $this->requireOpen($elections);
        $choices = session(VoterSession::CHOICES, []);

        if ($choices === []) {
            return redirect()->route('vote.ballot');
        }

        $candidates = Candidate::query()
            ->with('position')
            ->where('election_id', $election->id)
            ->whereIn('id', array_values($choices))
            ->get()
            ->keyBy('id');

        $lines = [];

        foreach ($choices as $positionId => $candidateId) {
            $candidate = $candidates->get($candidateId);

            if (! $candidate || (int) $candidate->position_id !== (int) $positionId) {
                session()->forget(VoterSession::CHOICES);

                return redirect()->route('vote.ballot')->with('error', 'Un des candidats choisis n\'est pas valide.');
            }

            $lines[] = [
                'position' => $candidate->position->name,
                'candidate' => $candidate->fullName(),
                'photo' => $candidate->photo_path ? asset($candidate->photo_path) : null,
            ];
        }

        return view('vote.confirm', [
            'election' => $election,
            'lines' => $lines,
        ]);
    }

    public function cast(ElectionService $elections, VotingService $voting): RedirectResponse
    {
        $election = $this->requireOpen($elections);
        $choices = session(VoterSession::CHOICES, []);

        if ($choices === []) {
            return redirect()->route('vote.ballot')->with('error', 'Sélectionnez au moins un candidat.');
        }

        try {
            $ballot = $voting->cast($election, $choices);
        } catch (VotingException $exception) {
            session()->forget(VoterSession::CHOICES);

            return redirect()
                ->route('vote.ballot')
                ->with('error', $this->message($exception->key));
        }

        $receipt = [
            'code' => $ballot->receipt_code,
            'voted_at' => $ballot->cast_at->timezone($election->timezone)->format('d/m/Y H:i'),
            'election' => $election->name,
        ];

        session()->invalidate();
        session()->regenerateToken();
        session()->put(VoterSession::RECEIPT, $receipt);

        return redirect()->route('vote.success');
    }

    public function success(): View|RedirectResponse
    {
        $receipt = session(VoterSession::RECEIPT);

        if (! is_array($receipt)) {
            return redirect()->route('home');
        }

        return view('vote.success', ['receipt' => $receipt]);
    }

    private function election(ElectionService $elections): ?Election
    {
        $election = $elections->publicElection();

        if ($election) {
            $elections->refreshStatus($election);
        }

        return $election;
    }

    private function requireOpen(ElectionService $elections): Election
    {
        $election = $this->election($elections);

        if (! $election || ! $elections->isAcceptingVotes($election)) {
            redirect()
                ->route('vote.ballot')
                ->with('error', 'Le scrutin n\'est actuellement pas ouvert.')
                ->throwResponse();
        }

        return $election;
    }

    private function message(string $key): string
    {
        return match ($key) {
            'closed' => 'Le scrutin n\'est actuellement pas ouvert.',
            'incomplete' => 'Sélectionnez au moins un candidat.',
            'invalid_candidate' => 'Un des candidats choisis n\'est pas valide.',
            'no_candidates' => 'Les candidatures ne sont pas encore publiées par la commission électorale.',
            default => 'Une erreur est survenue. Veuillez réessayer.',
        };
    }
}
