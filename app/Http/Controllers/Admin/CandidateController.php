<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CandidateStatus;
use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Position;
use App\Services\ElectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CandidateController extends Controller
{
    public function index(ElectionService $elections): View
    {
        $election = $elections->publicElection();

        return view('admin.candidates.index', [
            'election' => $election,
            'positions' => $election
                ? $election->positions()->where('is_active', true)->orderBy('display_order')->get()
                : collect(),
            'candidates' => $election
                ? $election->candidates()->with('position')->orderBy('position_id')->orderBy('last_name')->get()
                : collect(),
        ]);
    }

    public function store(Request $request, ElectionService $elections): RedirectResponse
    {
        $election = $elections->publicElection();
        abort_unless($election, 404);

        $data = $this->validated($request, $election->id);

        Candidate::query()->create([
            ...$data,
            'election_id' => $election->id,
            'status' => CandidateStatus::Active,
        ]);

        return back();
    }

    public function update(Request $request, Candidate $candidate, ElectionService $elections): RedirectResponse
    {
        $election = $elections->publicElection();
        abort_unless($election && $candidate->election_id === $election->id, 404);

        $candidate->update([
            'status' => $request->validate([
                'status' => ['required', Rule::enum(CandidateStatus::class)],
            ])['status'],
        ]);

        return back();
    }

    public function destroy(Candidate $candidate, ElectionService $elections): RedirectResponse
    {
        $election = $elections->publicElection();
        abort_unless($election && $candidate->election_id === $election->id, 404);

        if ($candidate->ballotChoices()->exists()) {
            return back()->with('error', 'Ce candidat a déjà reçu des voix. Retirez-le du scrutin au lieu de le supprimer.');
        }

        $candidate->delete();

        return back();
    }

    /**
     * @return array{position_id: int, first_name: string, last_name: string, post_name: ?string, display_order: int}
     */
    private function validated(Request $request, int $electionId): array
    {
        $data = $request->validate([
            'position_id' => ['required', 'integer', Rule::exists('positions', 'id')->where('election_id', $electionId)],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'post_name' => ['nullable', 'string', 'max:120'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        Position::query()->where('election_id', $electionId)->findOrFail($data['position_id']);

        return [
            'position_id' => (int) $data['position_id'],
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'post_name' => filled($data['post_name'] ?? null) ? trim($data['post_name']) : null,
            'display_order' => $data['display_order'] ?? 0,
        ];
    }
}
