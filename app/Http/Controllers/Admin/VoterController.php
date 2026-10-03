<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VoterStatus;
use App\Http\Controllers\Controller;
use App\Models\Voter;
use App\Services\ElectionService;
use App\Services\ImportVoterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VoterController extends Controller
{
    public function index(Request $request, ElectionService $elections): View
    {
        $election = $elections->publicElection();
        $term = trim($request->string('q')->toString());
        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $term).'%';

        $voters = $election
            ? $election->voters()
                ->when($term !== '', fn ($query) => $query->where('matricule', 'like', $like))
                ->orderBy('matricule')
                ->paginate(25)
                ->withQueryString()
            : null;

        return view('admin.voters.index', [
            'election' => $election,
            'voters' => $voters,
            'q' => $term,
        ]);
    }

    public function store(Request $request, ElectionService $elections): RedirectResponse
    {
        $election = $elections->publicElection();
        abort_unless($election, 404);

        $data = $request->validate([
            'matricule' => ['required', 'string', 'max:64'],
            'last_name' => ['required', 'string', 'max:120'],
            'post_name' => ['nullable', 'string', 'max:120'],
            'first_name' => ['required', 'string', 'max:120'],
            'faculty' => ['nullable', 'string', 'max:120'],
            'promotion' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);

        $matricule = Voter::normalizeMatricule($data['matricule']);

        $exists = Voter::query()
            ->where('election_id', $election->id)
            ->where('matricule', $matricule)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Ce matricule est déjà enregistré pour cette élection.');
        }

        Voter::query()->create([
            'election_id' => $election->id,
            'matricule' => $matricule,
            'last_name' => trim($data['last_name']),
            'post_name' => filled($data['post_name'] ?? null) ? trim($data['post_name']) : null,
            'first_name' => trim($data['first_name']),
            'faculty' => filled($data['faculty'] ?? null) ? trim($data['faculty']) : null,
            'promotion' => filled($data['promotion'] ?? null) ? trim($data['promotion']) : null,
            'phone' => filled($data['phone'] ?? null) ? trim($data['phone']) : null,
            'status' => VoterStatus::Eligible,
        ]);

        return back();
    }

    public function import(Request $request, ElectionService $elections, ImportVoterService $importer): RedirectResponse
    {
        $election = $elections->publicElection();
        abort_unless($election, 404);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:4096'],
        ]);

        $report = $importer->import($election, $request->file('file'));

        return back()->with('import', $report);
    }

    public function update(Request $request, Voter $voter, ElectionService $elections): RedirectResponse
    {
        $election = $elections->publicElection();
        abort_unless($election && $voter->election_id === $election->id, 404);

        $voter->update([
            'status' => $request->validate([
                'status' => ['required', Rule::enum(VoterStatus::class)],
            ])['status'],
        ]);

        return back();
    }
}
