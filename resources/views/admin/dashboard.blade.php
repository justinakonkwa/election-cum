<x-layouts.admin title="Tableau de bord">
    <h1 class="text-2xl font-semibold text-blue-950">Tableau de bord</h1>
    @if (! $election)
        <p class="mt-4 text-slate-600">Aucune élection n'est configurée.</p>
    @else
        <p class="mt-2 text-slate-600">{{ $election->name }} — {{ $election->status->label() }}</p>
        <div class="mt-6">
            <article class="rounded-2xl bg-white p-4 ring-1 ring-slate-200">
                <p class="text-xs uppercase tracking-wide text-slate-500">Votes enregistrés</p>
                <p class="mt-2 text-2xl font-semibold">{{ $votes }}</p>
            </article>
        </div>

        <form method="POST" action="{{ route('admin.election.status') }}" class="mt-6 flex flex-wrap items-end gap-3 rounded-2xl bg-white p-4 ring-1 ring-slate-200">
            @csrf
            <div>
                <label for="status" class="block text-sm font-medium">Statut du scrutin</label>
                <select id="status" name="status" class="mt-2 min-h-12 rounded-xl border border-slate-300 px-3">
                    @foreach (\App\Enums\ElectionStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected($election->status === $status)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="min-h-12 rounded-xl bg-blue-800 px-4 font-semibold text-white">Enregistrer</button>
        </form>

        <section class="mt-6 rounded-2xl bg-white p-4 ring-1 ring-slate-200">
            <h2 class="text-lg font-semibold">Résultats par candidat</h2>
            @if ($tallies === null)
                <p class="mt-2 text-sm text-slate-600">Les résultats restent masqués jusqu'à la clôture du scrutin.</p>
            @else
                <ul class="mt-4 space-y-3">
                    @foreach ($candidates->groupBy('position_id') as $group)
                        <li>
                            <p class="text-sm font-medium text-slate-500">{{ $group->first()->position->name }}</p>
                            <ul class="mt-2 space-y-1">
                                @foreach ($group as $candidate)
                                    @php
                                        $count = $tallies->get($candidate->id, 0);
                                        $percent = $ballots > 0 ? round($count / $ballots * 100, 2) : 0;
                                    @endphp
                                    <li class="flex justify-between gap-4 text-sm">
                                        <span>{{ $candidate->fullName() }}</span>
                                        <span>{{ $count }} voix · {{ number_format($percent, 2, ',', ' ') }} %</span>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endif
</x-layouts.admin>
