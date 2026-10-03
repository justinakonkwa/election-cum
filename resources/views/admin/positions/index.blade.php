<x-layouts.admin title="Postes">
    <h1 class="text-2xl font-semibold text-blue-950">Postes</h1>
    <p class="mt-2 text-sm text-slate-600">Chaque poste recevra un choix de l'électeur.</p>

    @if ($election)
        <form method="POST" action="{{ $editing ? route('admin.positions.update', $editing) : route('admin.positions.store') }}" class="mt-6 grid gap-3 rounded-2xl bg-white p-4 ring-1 ring-slate-200 sm:grid-cols-2">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif
            <div>
                <label for="name" class="block text-sm font-medium">Nom du poste</label>
                <input id="name" name="name" value="{{ old('name', $editing->name ?? '') }}" required class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-3">
            </div>
            <div>
                <label for="display_order" class="block text-sm font-medium">Ordre d'affichage</label>
                <input id="display_order" name="display_order" type="number" min="0" value="{{ old('display_order', $editing->display_order ?? 0) }}" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-3">
            </div>
            <div class="sm:col-span-2">
                <label for="description" class="block text-sm font-medium">Description</label>
                <input id="description" name="description" value="{{ old('description', $editing->description ?? '') }}" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-3">
            </div>
            @if ($editing)
                <label class="flex items-center gap-2 text-sm sm:col-span-2">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editing->is_active))>
                    Poste actif
                </label>
            @endif
            <button type="submit" class="min-h-12 rounded-xl bg-blue-800 px-4 font-semibold text-white sm:col-span-2">{{ $editing ? 'Mettre à jour' : 'Ajouter le poste' }}</button>
        </form>

        <ul class="mt-6 divide-y divide-slate-200 rounded-2xl bg-white ring-1 ring-slate-200">
            @forelse ($positions as $position)
                <li class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                    <div>
                        <p class="font-medium">{{ $position->name }}</p>
                        <p class="text-xs text-slate-500">{{ $position->candidates_count }} candidat(s) · {{ $position->is_active ? 'actif' : 'inactif' }}</p>
                    </div>
                    <div class="flex gap-3 text-sm">
                        <a class="underline" href="{{ route('admin.positions.index', ['edit' => $position->id]) }}">Modifier</a>
                        <form method="POST" action="{{ route('admin.positions.destroy', $position) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-700 underline">Supprimer</button>
                        </form>
                    </div>
                </li>
            @empty
                <li class="px-4 py-6 text-sm text-slate-500">Aucun poste pour le moment.</li>
            @endforelse
        </ul>
    @endif
</x-layouts.admin>
