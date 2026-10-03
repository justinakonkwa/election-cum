<x-layouts.admin title="Candidats">
    <h1 class="text-2xl font-semibold text-blue-950">Candidats</h1>
    <p class="mt-2 text-sm text-slate-600">Saisissez les personnes réellement candidates. L'électeur en choisit une par poste.</p>

    @if ($election)
        <form method="POST" action="{{ route('admin.candidates.store') }}" class="mt-6 grid gap-3 rounded-2xl bg-white p-4 ring-1 ring-slate-200 sm:grid-cols-2">
            @csrf
            <div class="sm:col-span-2">
                <label for="position_id" class="block text-sm font-medium">Poste</label>
                <select id="position_id" name="position_id" required class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-3">
                    <option value="">Choisir un poste</option>
                    @foreach ($positions as $position)
                        <option value="{{ $position->id }}" @selected((string) old('position_id') === (string) $position->id)>{{ $position->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="last_name" class="block text-sm font-medium">Nom</label>
                <input id="last_name" name="last_name" value="{{ old('last_name') }}" required class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-3">
            </div>
            <div>
                <label for="post_name" class="block text-sm font-medium">Postnom</label>
                <input id="post_name" name="post_name" value="{{ old('post_name') }}" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-3">
            </div>
            <div>
                <label for="first_name" class="block text-sm font-medium">Prénom</label>
                <input id="first_name" name="first_name" value="{{ old('first_name') }}" required class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-3">
            </div>
            <div>
                <label for="display_order" class="block text-sm font-medium">Ordre</label>
                <input id="display_order" name="display_order" type="number" min="0" value="{{ old('display_order', 0) }}" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-3">
            </div>
            <button type="submit" class="min-h-12 rounded-xl bg-blue-800 px-4 font-semibold text-white sm:col-span-2">Ajouter le candidat</button>
        </form>

        <ul class="mt-6 divide-y divide-slate-200 rounded-2xl bg-white ring-1 ring-slate-200">
            @forelse ($candidates as $candidate)
                <li class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                    <div class="flex items-center gap-3">
                        @if ($candidate->photo_path)
                            <img src="{{ asset($candidate->photo_path) }}" alt="" class="h-12 w-10 rounded-md object-cover object-top">
                        @endif
                        <div>
                        <p class="font-medium">{{ $candidate->fullName() }}</p>
                        <p class="text-xs text-slate-500">{{ $candidate->position->name }} · {{ $candidate->status === \App\Enums\CandidateStatus::Active ? 'actif' : 'retiré' }}</p>
                        </div>
                    </div>
                    <div class="flex gap-3 text-sm">
                        <form method="POST" action="{{ route('admin.candidates.update', $candidate) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="{{ $candidate->status === \App\Enums\CandidateStatus::Active ? 'withdrawn' : 'active' }}">
                            <button type="submit" class="underline">{{ $candidate->status === \App\Enums\CandidateStatus::Active ? 'Retirer' : 'Réactiver' }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.candidates.destroy', $candidate) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-700 underline">Supprimer</button>
                        </form>
                    </div>
                </li>
            @empty
                <li class="px-4 py-6 text-sm text-slate-500">Aucun candidat pour le moment.</li>
            @endforelse
        </ul>
    @endif
</x-layouts.admin>
