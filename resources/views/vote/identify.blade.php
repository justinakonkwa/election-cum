<x-layouts.public title="Votre matricule">
    <p class="text-[11px] font-medium uppercase tracking-[0.16em] text-brass">Bulletin de vote</p>
    <h1 class="mt-1 font-serif text-3xl leading-tight text-ink">Votre matricule</h1>

    @if (! $open)
        <section class="mt-5 rounded-3xl border border-line bg-white px-6 py-8 text-center">
            <p class="text-sm text-slate-700">Le scrutin n'est actuellement pas ouvert.</p>
            <a href="{{ route('home') }}" class="mt-5 inline-flex min-h-12 items-center text-sm font-semibold text-navy">Retour à l'accueil</a>
        </section>
    @else
        <p class="mt-2 text-sm leading-6 text-slate-600">Saisissez le matricule figurant sur la liste électorale. Une seule voix est admise.</p>
        <form method="POST" action="{{ route('vote.identify.store') }}" class="mt-6 rounded-3xl border border-line bg-white p-5">
            @csrf
            <label for="matricule" class="block text-sm font-medium text-ink">Votre matricule UOB</label>
            <input
                id="matricule"
                name="matricule"
                value="{{ old('matricule') }}"
                required
                autocomplete="off"
                inputmode="text"
                autocapitalize="characters"
                spellcheck="false"
                class="mt-2 min-h-14 w-full rounded-2xl border border-line bg-paper px-4 text-lg tracking-wide text-ink"
            >
            <button type="submit" class="mt-4 flex min-h-14 w-full items-center justify-center rounded-2xl bg-navy text-base font-semibold text-white">Continuer</button>
        </form>
    @endif
</x-layouts.public>
