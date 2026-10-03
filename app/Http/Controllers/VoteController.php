<?php

namespace App\Http\Controllers;

use App\Exceptions\VotingException;
use App\Http\Requests\IdentifyVoterRequest;
use App\Http\Requests\ReviewVoteRequest;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Voter;
use App\Services\ElectionService;
use App\Services\VoterService;
use App\Services\VotingService;
use App\Support\VoterSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VoteController extends Controller
{
    public function identify(ElectionService $elections): View|RedirectResponse
    {
        $election = $this->election($elections);
        $open = $election && $elections->isAcceptingVotes($election);

        if ($open && $this->currentVoter($election)) {
            return redirect()->route('vote.ballot');
        }

        return view('vote.identify', [
            'open' => $open,
        ]);
    }

    public function storeIdentity(IdentifyVoterRequest $request, ElectionService $elections, VoterService $voters): RedirectResponse
    {
        $election = $this->requireOpen($elections, 'vote.identify');

        try {
            $voter = $voters->findEligible($election, $request->validated('matricule'));
        } catch (VotingException $exception) {
            return back()->withInput()->with('error', $this->message($exception->key));
        }

        VoterSession::start($election, $voter);

        return redirect()->route('vote.ballot');
    }

    public function ballot(ElectionService $elections, VotingService $voting): View|RedirectResponse
    {
        $election = $this->election($elections);
        $open = $election && $elections->isAcceptingVotes($election);

        if ($open) {
            $this->voter($election);
        }

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
        $this->voter($election);

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
        $this->voter($election);
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
        $voter = $this->voter($election);
        $choices = session(VoterSession::CHOICES, []);

        if ($choices === []) {
            return redirect()->route('vote.ballot')->with('error', 'Sélectionnez un candidat pour chaque poste.');
        }

        try {
            $ballot = $voting->cast($election, $voter, $choices);
        } catch (VotingException $exception) {
            VoterSession::clearIdentity();

            return redirect()
                ->route('vote.identify')
                ->with('error', $this->message($exception->key));
        }

        VoterSession::clearIdentity();
        session()->put(VoterSession::RECEIPT, [
            'code' => $ballot->receipt_code,
            'voted_at' => $ballot->cast_at->timezone($election->timezone)->format('d/m/Y H:i'),
            'election' => $election->name,
        ]);

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

    private function requireOpen(ElectionService $elections, string $route = 'vote.ballot'): Election
    {
        $election = $this->election($elections);

        if (! $election || ! $elections->isAcceptingVotes($election)) {
            redirect()
                ->route($route)
                ->with('error', 'Le scrutin n\'est actuellement pas ouvert.')
                ->throwResponse();
        }

        return $election;
    }

    private function voter(Election $election): Voter
    {
        $voter = $this->currentVoter($election);

        if (! $voter) {
            VoterSession::clearIdentity();

            redirect()
                ->route('vote.identify')
                ->with('error', 'Saisissez votre matricule pour continuer.')
                ->throwResponse();
        }

        if ($voter->has_voted) {
            VoterSession::clearIdentity();

            redirect()
                ->route('vote.identify')
                ->with('error', $this->message('already_voted'))
                ->throwResponse();
        }

        return $voter;
    }

    private function currentVoter(Election $election): ?Voter
    {
        $payload = VoterSession::get();

        if (! $payload || $payload['election_id'] !== $election->id || $payload['expires_at'] < now()->getTimestamp()) {
            return null;
        }

        return Voter::query()
            ->whereKey($payload['voter_id'])
            ->where('election_id', $election->id)
            ->first();
    }

    private function message(string $key): string
    {
        return match ($key) {
            'closed' => 'Le scrutin n\'est actuellement pas ouvert.',
            'incomplete' => 'Sélectionnez un candidat pour chaque poste.',
            'invalid_candidate' => 'Un des candidats choisis n\'est pas valide.',
            'no_candidates' => 'Les candidatures ne sont pas encore publiées par la commission électorale.',
            'unknown' => 'Ce matricule ne figure pas sur la liste électorale.',
            'ineligible' => 'Ce matricule n\'est pas autorisé à voter.',
            'already_voted' => 'Ce matricule a déjà voté.',
            default => 'Une erreur est survenue. Veuillez réessayer.',
        };
    }
}
