<x-layouts.admin title="Connexion commission">
    <section class="mx-auto max-w-md rounded-2xl bg-white p-6 ring-1 ring-slate-200">
        <h1 class="text-2xl font-semibold text-blue-950">Espace commission</h1>
        <p class="mt-2 text-sm text-slate-600">Réservé à l'administration du scrutin.</p>
        <form method="POST" action="{{ route('admin.login.store') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium">Adresse e-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-4">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium">Mot de passe</label>
                <input id="password" name="password" type="password" required autocomplete="current-password" class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 px-4">
            </div>
            <button type="submit" class="flex min-h-12 w-full items-center justify-center rounded-xl bg-blue-800 font-semibold text-white">Entrer</button>
        </form>
    </section>
</x-layouts.admin>
