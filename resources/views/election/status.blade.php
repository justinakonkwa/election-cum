<x-layouts.public title="Statut du scrutin">
    <p class="text-[11px] font-medium uppercase tracking-[0.16em] text-brass">Scrutin</p>
    <h1 class="mt-1 font-serif text-3xl text-ink">Statut du scrutin</h1>
    @if (! $election)
        <p class="mt-3 text-slate-600">Aucune élection n'est configurée.</p>
    @else
        <section class="mt-5 rounded-3xl border border-line bg-white px-5 py-5">
            <p class="font-serif text-2xl text-ink">{{ $election->name }}</p>
            <p class="mt-1 text-sm text-slate-600">{{ $election->institution }}</p>
            <dl class="mt-5 divide-y divide-line text-sm">
                @php
                    $opens = $election->start_at->timezone($election->timezone);
                    $closes = $election->end_at->timezone($election->timezone);
                @endphp
                <div class="flex items-start justify-between gap-4 py-3">
                    <dt class="text-slate-500">Ouverture</dt>
                    <dd class="text-right">{{ $opens->translatedFormat('l d F Y') }} · {{ $opens->format('H\hi') }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-3">
                    <dt class="text-slate-500">Clôture</dt>
                    <dd class="text-right">{{ $closes->translatedFormat('l d F Y') }} · {{ $closes->format('H\hi') }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 py-3">
                    <dt class="text-slate-500">Statut</dt>
                    <dd class="font-semibold text-navy">{{ $election->status->label() }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 py-3">
                    <dt class="text-slate-500">Postes</dt>
                    <dd>{{ $positions }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 py-3">
                    <dt class="text-slate-500">Candidats</dt>
                    <dd>{{ $candidates }}</dd>
                </div>
            </dl>
            <p class="mt-2 text-sm text-slate-700">
                @if ($election->status === \App\Enums\ElectionStatus::Open)
                    Le scrutin est actuellement ouvert.
                @else
                    Le scrutin est actuellement fermé.
                @endif
            </p>
        </section>
        <a href="{{ route('home') }}" class="mt-5 flex min-h-14 items-center justify-center rounded-2xl bg-navy text-base font-semibold text-white">Retour à l'accueil</a>
    @endif
</x-layouts.public>
