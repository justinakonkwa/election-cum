<x-layouts.public title="Choisissez vos candidats">
    <div class="mb-5 text-center">
        <x-logo class="mx-auto h-20 w-20" />
        <p class="mt-4 text-[11px] font-medium uppercase tracking-[0.16em] text-brass">Bulletin de vote</p>
        <h1 class="mt-1 font-serif text-3xl leading-tight text-ink">Choisissez vos candidats</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Un seul candidat par poste. Vous pouvez laisser un poste vide. Le vote est enregistré, puis la session se ferme.</p>
    </div>

    @if (! $open)
        <section class="rounded-3xl border border-line bg-white px-6 py-8 text-center">
            <p class="text-sm text-slate-700">Le scrutin n'est actuellement pas ouvert.</p>
            <a href="{{ route('home') }}" class="mt-5 inline-flex min-h-12 items-center text-sm font-semibold text-navy">Retour à l'accueil</a>
        </section>
    @elseif ($positions->isEmpty())
        <section class="rounded-3xl border border-line bg-white px-6 py-8">
            <p class="text-sm text-slate-700">Les candidatures ne sont pas encore publiées par la commission électorale.</p>
        </section>
    @else
        <form method="POST" action="{{ route('vote.review') }}" class="space-y-5 pb-4">
            @csrf
            @foreach ($positions as $position)
                <fieldset class="rounded-3xl border border-line bg-white p-4">
                    <legend class="w-full px-1 text-base font-semibold leading-snug text-ink">{{ $position->name }}</legend>
                    @if ($position->description)
                        <p class="mt-1 px-1 text-sm text-slate-500">{{ $position->description }}</p>
                    @endif
                    <div class="mt-3 space-y-2">
                        @foreach ($position->candidates as $candidate)
                            <label class="block">
                                <input
                                    type="radio"
                                    name="choices[{{ $position->id }}]"
                                    value="{{ $candidate->id }}"
                                    class="choice sr-only"
                                    @checked((int) ($selected[$position->id] ?? 0) === $candidate->id)
                                >
                                <span class="choice-card flex min-h-[5.5rem] items-center gap-3 rounded-2xl border border-line px-3 py-3">
                                    @if ($candidate->photo_path)
                                        <img src="{{ asset($candidate->photo_path) }}" alt="" class="h-20 w-16 shrink-0 rounded-xl object-cover object-top">
                                    @else
                                        <span class="grid h-20 w-16 shrink-0 place-items-center rounded-xl bg-paper font-serif text-lg text-navy" aria-hidden="true">{{ mb_substr($candidate->fullName(), 0, 1) }}</span>
                                    @endif
                                    <span class="min-w-0 flex-1 text-[15px] font-semibold leading-snug text-ink">{{ $candidate->fullName() }}</span>
                                    <span class="choice-mark h-6 w-6 shrink-0 rounded-full border-2 border-slate-300" aria-hidden="true"></span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach

            <div class="sticky bottom-0 z-10 -mx-4 border-t border-line bg-paper/95 px-4 py-3 backdrop-blur-none">
                <button type="submit" class="flex min-h-14 w-full items-center justify-center rounded-2xl bg-navy text-base font-semibold text-white">Continuer</button>
            </div>
        </form>
    @endif
</x-layouts.public>
