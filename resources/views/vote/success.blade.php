<x-layouts.public title="Vote enregistré">
    <section class="rounded-3xl border border-line bg-white px-6 py-8 text-center">
        <x-logo class="mx-auto h-32 w-32" />
        <div class="mx-auto mt-4 grid h-14 w-14 place-items-center rounded-full bg-navy text-white" aria-hidden="true">
            <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M5 12.5 9.2 17 19 7" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <h1 class="mt-5 font-serif text-3xl leading-tight text-ink">Vote enregistré avec succès.</h1>
        <p class="mt-3 text-sm leading-6 text-slate-600">Votre vote a été enregistré avec succès. Merci d'avoir participé aux {{ $receipt['election'] }}.</p>
        <dl class="mt-6 rounded-2xl bg-paper px-4 py-4 text-left text-sm">
            <div>
                <dt class="text-[11px] font-medium uppercase tracking-[0.14em] text-brass">Numéro de confirmation</dt>
                <dd class="mt-1 font-mono text-lg font-semibold tracking-wide text-ink">{{ $receipt['code'] }}</dd>
            </div>
            <div class="mt-4">
                <dt class="text-[11px] font-medium uppercase tracking-[0.14em] text-brass">Reçu le</dt>
                <dd class="mt-1 text-ink">{{ $receipt['voted_at'] }}</dd>
            </div>
        </dl>
        <p class="mt-4 text-xs leading-5 text-slate-500">Ce numéro confirme l'enregistrement. Il ne révèle pas vos candidats.</p>
        <a href="{{ route('home') }}" class="mt-6 flex min-h-14 items-center justify-center rounded-2xl bg-navy text-base font-semibold text-white">Retour à l'accueil</a>
    </section>
</x-layouts.public>
