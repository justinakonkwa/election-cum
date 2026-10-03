<x-layouts.admin title="Électeurs">
    <h1 class="text-2xl font-semibold text-blue-950">Électeurs</h1>
    <p class="mt-2 text-sm text-slate-600">Registre de la commission. Le vote en ligne ne demande pas de matricule.</p>

    @if ($election)
        <form method="POST" action="{{ route('admin.voters.store') }}" class="mt-6 grid gap-3 rounded-2xl bg-white p-4 ring-1 ring-slate-200 sm:grid-cols-2">
            @csrf
            <div>
                <label for="matricule" class="block text-sm font-medium">Matricule</label>
                <input id="matricule" name="matricule" value="{{ old('matricule') }}" required class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-3">
            </div>
            <div>
                <label for="promotion" class="block text-sm font-medium">Promotion</label>
                <input id="promotion" name="promotion" value="{{ old('promotion') }}" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-3">
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
                <label for="faculty" class="block text-sm font-medium">Faculté</label>
                <input id="faculty" name="faculty" value="{{ old('faculty') }}" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-3">
            </div>
            <button type="submit" class="min-h-12 rounded-xl bg-blue-800 px-4 font-semibold text-white sm:col-span-2">Ajouter l'électeur</button>
        </form>

        <form method="POST" action="{{ route('admin.voters.import') }}" enctype="multipart/form-data" class="mt-4 rounded-2xl bg-white p-4 ring-1 ring-slate-200">
            @csrf
            <label for="file" class="block text-sm font-medium">Importer un CSV</label>
            <p class="mt-1 text-xs text-slate-500">Colonnes : matricule, nom, postnom, prenom, faculte, promotion, telephone.</p>
            <input id="file" name="file" type="file" accept=".csv,.txt,text/csv" required class="mt-3 block w-full text-sm">
            <button type="submit" class="mt-3 min-h-12 rounded-xl bg-slate-800 px-4 font-semibold text-white">Importer</button>
        </form>

        @if (session('import'))
            @php $report = session('import'); @endphp
            <div class="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-sm">
                <p>Total lignes : {{ $report['total'] }}</p>
                <p>Importés : {{ $report['imported'] }}</p>
                <p>Doublons : {{ $report['duplicates'] }}</p>
                <p>Erreurs : {{ $report['errors'] }}</p>
                @foreach ($report['messages'] as $message)
                    <p class="mt-1 text-slate-600">{{ $message }}</p>
                @endforeach
            </div>
        @endif

        <form method="GET" action="{{ route('admin.voters.index') }}" class="mt-6 flex gap-2">
            <label for="q" class="sr-only">Rechercher un matricule</label>
            <input id="q" name="q" value="{{ $q }}" placeholder="Matricule" class="min-h-12 flex-1 rounded-xl border border-slate-300 px-3">
            <button type="submit" class="min-h-12 rounded-xl bg-white px-4 ring-1 ring-slate-300">Chercher</button>
        </form>

        <ul class="mt-4 divide-y divide-slate-200 rounded-2xl bg-white ring-1 ring-slate-200">
            @forelse ($voters as $voter)
                <li class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                    <div>
                        <p class="font-medium">{{ $voter->matricule }}</p>
                        <p class="text-sm text-slate-600">{{ $voter->last_name }} {{ $voter->post_name }} {{ $voter->first_name }}</p>
                    </div>
                    @if ($voter->has_voted)
                        <p class="text-sm font-medium text-navy">A voté</p>
                    @endif
                </li>
            @empty
                <li class="px-4 py-6 text-sm text-slate-500">Aucun électeur.</li>
            @endforelse
        </ul>
        @if ($voters)
            <div class="mt-4">{{ $voters->links() }}</div>
        @endif
    @endif
</x-layouts.admin>
