<x-layouts.public title="Confirmer le vote">
    <p class="text-[11px] font-medium uppercase tracking-[0.16em] text-brass">Confirmation</p>
    <h1 class="mt-1 font-serif text-3xl leading-tight text-ink">Vous êtes sur le point de voter pour :</h1>

    <ul class="mt-5 space-y-3">
        @foreach ($lines as $line)
            <li class="flex items-center gap-3 rounded-2xl border border-line bg-white px-3 py-3">
                @if ($line['photo'])
                    <img src="{{ $line['photo'] }}" alt="" class="h-16 w-14 shrink-0 rounded-xl object-cover object-top">
                @endif
                <div class="min-w-0">
                    <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-brass">{{ $line['position'] }}</p>
                    <p class="mt-1 text-base font-semibold leading-snug text-ink">{{ $line['candidate'] }}</p>
                </div>
            </li>
        @endforeach
    </ul>

    <p class="mt-5 rounded-2xl border border-[#e7d7b4] bg-[#fbf6ec] px-4 py-3 text-sm leading-6 text-ink">Attention : après confirmation, votre vote ne pourra plus être modifié.</p>

    <div class="mt-5 grid gap-3">
        <form method="POST" action="{{ route('vote.cast') }}">
            @csrf
            <button type="submit" class="flex min-h-14 w-full items-center justify-center rounded-2xl bg-navy text-base font-semibold text-white">Confirmer mon vote</button>
        </form>
        <a href="{{ route('vote.ballot') }}" class="flex min-h-14 items-center justify-center rounded-2xl border border-line bg-white text-base font-semibold text-navy">Retour</a>
    </div>
</x-layouts.public>
