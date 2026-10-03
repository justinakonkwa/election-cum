<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Services\ElectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function index(Request $request, ElectionService $elections): View
    {
        $election = $elections->publicElection();
        $editing = null;

        if ($election && $request->filled('edit')) {
            $editing = Position::query()
                ->where('election_id', $election->id)
                ->find($request->integer('edit'));
        }

        return view('admin.positions.index', [
            'election' => $election,
            'positions' => $election
                ? $election->positions()->withCount('candidates')->orderBy('display_order')->orderBy('id')->get()
                : collect(),
            'editing' => $editing,
        ]);
    }

    public function store(Request $request, ElectionService $elections): RedirectResponse
    {
        $election = $elections->publicElection();
        abort_unless($election, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        Position::query()->create([
            'election_id' => $election->id,
            'name' => trim($data['name']),
            'description' => $data['description'] ?? null,
            'display_order' => $data['display_order'] ?? 0,
            'is_active' => true,
        ]);

        return back();
    }

    public function update(Request $request, Position $position, ElectionService $elections): RedirectResponse
    {
        $election = $elections->publicElection();
        abort_unless($election && $position->election_id === $election->id, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $position->update([
            'name' => trim($data['name']),
            'description' => $data['description'] ?? null,
            'display_order' => $data['display_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.positions.index');
    }

    public function destroy(Position $position, ElectionService $elections): RedirectResponse
    {
        $election = $elections->publicElection();
        abort_unless($election && $position->election_id === $election->id, 404);

        if ($position->candidates()->exists()) {
            return back()->with('error', 'Retirez d\'abord les candidats de ce poste.');
        }

        $position->delete();

        return back();
    }
}
