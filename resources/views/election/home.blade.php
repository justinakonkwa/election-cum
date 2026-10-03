<x-layouts.public title="Élections CUM 2026">
    @if (! $election)
        <section class="rounded-3xl border border-line bg-white px-6 py-8">
            <h1 class="font-serif text-3xl text-ink">Élections CUM 2026</h1>
            <p class="mt-3 text-slate-600">Aucune élection n'est configurée.</p>
        </section>
    @else
        @php
            $clock = $remaining ? explode(' : ', $remaining) : null;
        @endphp
        <section class="overflow-hidden rounded-3xl border border-line bg-white">
            <div class="bg-navy px-6 pb-7 pt-7 text-center text-white">
                <x-logo class="mx-auto h-36 w-36" />
                <p class="mt-4 text-[11px] font-medium uppercase tracking-[0.18em] text-[#e4d3ae]">{{ $election->organization }}</p>
                <h1 class="mt-2 font-serif text-[2rem] leading-tight">{{ $election->name }}</h1>
                <p class="mt-2 text-sm text-blue-100">{{ $election->institution }}</p>
            </div>

            <div class="px-6 py-6">
                <p class="text-center text-[11px] font-medium uppercase tracking-[0.16em] text-brass">
                    {{ $election->start_at->timezone($election->timezone)->translatedFormat('l d F Y') }}
                </p>
                <p class="mt-1 text-center font-serif text-2xl text-ink">
                    {{ $election->start_at->timezone($election->timezone)->format('H\hi') }}
                    <span class="text-brass">—</span>
                    {{ $election->end_at->timezone($election->timezone)->format('H\hi') }}
                </p>

                <div class="mt-6" @if ($target && $clock) x-data="{ h: @js($clock[0]), m: @js($clock[1]), s: @js($clock[2]), target: {{ $target }}, tick() { const diff = Math.max(0, this.target - Math.floor(Date.now() / 1000)); this.h = String(Math.floor(diff / 3600)).padStart(2, '0'); this.m = String(Math.floor((diff % 3600) / 60)).padStart(2, '0'); this.s = String(diff % 60).padStart(2, '0'); } }" x-init="tick(); setInterval(() => this.tick(), 1000)" @endif>
                    @if ($phase === 'before')
                        <p class="text-center text-sm text-slate-600">Le vote commence dans</p>
                    @elseif ($phase === 'open')
                        <p class="text-center text-sm font-medium text-navy">Le vote est actuellement ouvert.</p>
                        <p class="mt-1 text-center text-sm text-slate-600">Il reste</p>
                    @elseif ($phase === 'suspended')
                        <p class="text-center text-sm font-medium text-ink">Le scrutin est suspendu.</p>
                    @else
                        <p class="text-center text-sm font-medium text-ink">Le scrutin est clôturé.</p>
                    @endif

                    @if ($clock && in_array($phase, ['before', 'open'], true))
                        <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-2xl bg-paper px-2 py-3">
                                <p class="font-serif text-3xl text-ink" x-text="h">{{ $clock[0] }}</p>
                                <p class="mt-1 text-[11px] uppercase tracking-wider text-slate-500">heures</p>
                            </div>
                            <div class="rounded-2xl bg-paper px-2 py-3">
                                <p class="font-serif text-3xl text-ink" x-text="m">{{ $clock[1] }}</p>
                                <p class="mt-1 text-[11px] uppercase tracking-wider text-slate-500">minutes</p>
                            </div>
                            <div class="rounded-2xl bg-paper px-2 py-3">
                                <p class="font-serif text-3xl text-ink" x-text="s">{{ $clock[2] }}</p>
                                <p class="mt-1 text-[11px] uppercase tracking-wider text-slate-500">secondes</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-6 grid gap-3">
                    <a href="{{ route('vote.ballot') }}" class="flex min-h-14 items-center justify-center rounded-2xl bg-navy px-4 text-base font-semibold text-white">Voter</a>
                    <a href="{{ route('election.status') }}" class="flex min-h-14 items-center justify-center rounded-2xl border border-line bg-white px-4 text-base font-semibold text-navy">Voir le statut</a>
                </div>
            </div>
        </section>
    @endif
</x-layouts.public>
