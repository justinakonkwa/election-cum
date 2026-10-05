<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rapport — {{ $election->name ?? 'Élections CUM' }}</title>
    <link rel="icon" href="{{ asset('images/logo-cum.png') }}?v=3" type="image/png">
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: #f4f1ea;
            color: #1c2430;
            font-family: Georgia, "Iowan Old Style", Palatino, serif;
        }

        .sheet {
            width: min(720px, calc(100% - 32px));
            margin: 32px auto 48px;
            background: #fff;
            padding: 36px 40px 28px;
        }

        header {
            display: flex;
            align-items: center;
            gap: 16px;
            padding-bottom: 16px;
            border-bottom: 2px solid #16325c;
        }

        header img {
            width: 72px;
            height: 72px;
            object-fit: contain;
        }

        .kicker {
            margin: 0;
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 11px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #8a7040;
        }

        h1 {
            margin: 4px 0 0;
            font-size: 28px;
            font-weight: 500;
            line-height: 1.1;
            color: #16325c;
        }

        .org {
            margin: 4px 0 0;
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 13px;
            color: #5c6570;
        }

        .meta {
            margin: 14px 0 8px;
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 13px;
            color: #5c6570;
        }

        .row {
            display: grid;
            grid-template-columns: 72px 1fr auto;
            gap: 16px;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #ece7df;
            break-inside: avoid;
        }

        .portrait, .fallback {
            width: 72px;
            height: 90px;
        }

        .portrait {
            object-fit: cover;
            object-position: center top;
        }

        .fallback {
            display: grid;
            place-items: center;
            background: #f4f1ea;
            color: #16325c;
            font-size: 24px;
        }

        .role {
            margin: 0;
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #8a7040;
        }

        h2 {
            margin: 3px 0 0;
            font-size: 20px;
            font-weight: 500;
            line-height: 1.2;
        }

        .desc {
            margin: 2px 0 0;
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 13px;
            color: #5c6570;
        }

        .votes {
            min-width: 72px;
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .votes strong {
            display: block;
            font-size: 22px;
            font-weight: 500;
            color: #16325c;
        }

        .votes span {
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 11px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #5c6570;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            margin-top: 22px;
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 13px;
        }

        .actions a {
            color: #16325c;
            text-underline-offset: 3px;
        }

        .colophon {
            margin: 18px 0 0;
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 11px;
            color: #8b8378;
        }

        @media (max-width: 420px) {
            .sheet { padding: 24px 18px; }
            h1 { font-size: 24px; }
        }

        @page { size: A4; margin: 14mm 16mm; }

        @media print {
            body { background: #fff; }
            .sheet { width: auto; margin: 0; padding: 0; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    <article class="sheet">
        <header>
            <x-logo width="72" height="72" />
            <div>
                <p class="kicker">Rapport du scrutin</p>
                <h1>{{ $election->name ?? 'Élections CUM' }}</h1>
                <p class="org">{{ $election->institution ?? 'Université Officielle de Bukavu' }} · {{ $election->organization ?? 'Club Univers Médical' }}</p>
            </div>
        </header>

        @if (! $election)
            <p class="meta">Aucune élection n'est configurée.</p>
        @else
            @php
                $closes = $election->end_at->timezone($election->timezone);
                $bulletin = $ballots.' '.($ballots > 1 ? 'bulletins' : 'bulletin');
            @endphp
            <p class="meta">{{ $closes->translatedFormat('d F Y') }} · {{ $election->status->label() }} · {{ $bulletin }}</p>

            @foreach ($positions as $position)
                @forelse ($position->candidates as $candidate)
                    @php
                        $name = mb_convert_case(mb_strtolower($candidate->fullName(), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                        $count = $visible ? (int) $tallies->get($candidate->id, 0) : null;
                    @endphp
                    <section class="row">
                        @if ($candidate->photo_path)
                            <img class="portrait" src="{{ asset($candidate->photo_path) }}" alt="">
                        @else
                            <div class="fallback" aria-hidden="true">{{ mb_substr($name, 0, 1) }}</div>
                        @endif
                        <div>
                            <p class="role">{{ $position->name }}</p>
                            <h2>{{ $name }}</h2>
                            @if ($position->description)
                                <p class="desc">{{ $position->description }}</p>
                            @endif
                        </div>
                        @if ($count !== null)
                            <p class="votes">
                                <strong>{{ $count }}</strong>
                                <span>voix</span>
                            </p>
                        @endif
                    </section>
                @empty
                    <section class="row">
                        <div class="fallback" aria-hidden="true">—</div>
                        <div>
                            <p class="role">{{ $position->name }}</p>
                            <h2>Aucun candidat</h2>
                        </div>
                    </section>
                @endforelse
            @endforeach
        @endif

        <div class="actions">
            <a href="{{ route('home') }}">Accueil</a>
            <a href="{{ asset('rapport-cum-2026.pdf') }}">Télécharger le PDF</a>
        </div>
        <p class="colophon">Commission électorale · {{ now()->timezone($election?->timezone ?? 'Africa/Lubumbashi')->translatedFormat('d F Y') }}</p>
    </article>
</body>
</html>
