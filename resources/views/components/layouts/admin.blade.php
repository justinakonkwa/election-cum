<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Commission' }}</title>
    <link rel="icon" href="{{ asset('images/logo-cum.png') }}?v=3" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper text-ink antialiased">
    <header class="border-b-4 border-brass bg-navy text-white">
        <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-3 px-5 py-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <x-logo class="h-14 w-14" />
                <span>
                    <span class="block text-[11px] uppercase tracking-[0.16em] text-[#e4d3ae]">Commission électorale</span>
                    <span class="mt-1 block text-sm">Élections CUM</span>
                </span>
            </a>
            @auth
                <nav class="flex flex-wrap gap-x-4 gap-y-2 text-sm text-blue-50">
                    <a href="{{ route('admin.dashboard') }}">Tableau de bord</a>
                    <a href="{{ route('admin.positions.index') }}">Postes</a>
                    <a href="{{ route('admin.candidates.index') }}">Candidats</a>
                    <a href="{{ route('admin.voters.index') }}">Électeurs</a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit">Quitter</button>
                    </form>
                </nav>
            @endauth
        </div>
    </header>
    <main class="mx-auto max-w-5xl px-4 py-6">
        @if (session('error'))
            <p role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</p>
        @endif
        @if ($errors->any())
            <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        {{ $slot }}
    </main>
</body>
</html>
