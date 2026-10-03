<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Élections CUM' }}</title>
    <link rel="icon" href="{{ asset('images/logo-cum.svg') }}?v=2" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper text-ink antialiased">
    <a href="#contenu" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-10 focus:bg-white focus:px-3 focus:py-2">Aller au contenu</a>
    <header class="border-b-4 border-brass bg-navy text-white">
        <div class="mx-auto flex max-w-lg items-center gap-3 px-5 py-4">
            <x-logo class="h-14 w-14" />
            <div class="min-w-0 leading-tight">
                <p class="text-[11px] font-medium uppercase tracking-[0.16em] text-[#e4d3ae]">Université Officielle de Bukavu</p>
                <p class="mt-1 truncate text-sm">Club Univers Médical</p>
            </div>
        </div>
    </header>
    <main id="contenu" class="mx-auto w-full max-w-lg px-4 py-6">
        @if (session('error'))
            <p role="alert" class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</p>
        @endif
        @if ($errors->any())
            <div role="alert" class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        {{ $slot }}
    </main>
    <footer class="mx-auto max-w-lg px-5 pb-10 pt-2 text-center text-xs leading-5 text-slate-500">
        <p>Commission électorale · CUM 2026</p>
        <p class="mt-1"><a class="text-navy underline decoration-brass underline-offset-4" href="{{ route('admin.login') }}">Espace commission</a></p>
    </footer>
</body>
</html>
