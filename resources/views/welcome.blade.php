<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Coordinamento Esami</title>
    <meta name="description" content="Pianifica gli appelli universitari, verifica le sovrapposizioni e consulta il calendario delle sessioni d'esame.">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-shell !bg-background">
    <header class="border-b border-border">
        <div class="app-container flex min-h-20 items-center justify-between gap-4 py-4">
            <a href="/" class="flex items-center gap-3 text-base font-semibold text-foreground no-underline sm:text-xl">
                <x-application-logo class="size-9 shrink-0" />
                Coordinamento Esami
            </a>
            <x-ui.button :href="route('login')">Accedi</x-ui.button>
        </div>
    </header>
    <main class="app-container grow landing-dot-field">
        <section class="mx-auto max-w-4xl py-16 text-center sm:py-24">
            <h1 class="text-4xl font-semibold tracking-tight sm:text-5xl lg:text-6xl">Le date degli esami,<br class="hidden sm:block"> coordinate in un unico posto.</h1>
            <p class="mx-auto !mt-6 max-w-2xl text-lg leading-relaxed text-muted-foreground">
                Uno spazio condiviso per coordinare gli appelli universitari: organizza le sessioni,
                verifica le sovrapposizioni e consulta il calendario degli esami.
            </p>
            <x-ui.button :href="route('login')" class="mt-4">Accedi <x-ui.icon name="arrow-right" class="ml-2" /></x-ui.button>
        </section>
        <section aria-label="Funzionalità di Coordinamento Esami" class="grid gap-6 pb-16 md:grid-cols-3 sm:pb-24">
            <x-ui.card>
                <x-ui.card-content class="!p-6">
                    <x-ui.icon name="calendar" class="!size-7 text-accent-foreground" />
                    <h2 class="!mt-5 text-xl font-semibold">Pianifica gli appelli</h2>
                    <p class="!mb-0 text-muted-foreground">Gestisci date, orari e aule degli insegnamenti nelle finestre di inserimento previste per ogni sessione.</p>
                </x-ui.card-content>
            </x-ui.card>
            <x-ui.card>
                <x-ui.card-content class="!p-6">
                    <x-ui.icon name="alert-triangle" class="!size-7 text-accent-foreground" />
                    <h2 class="!mt-5 text-xl font-semibold">Controlla le sovrapposizioni</h2>
                    <p class="!mb-0 text-muted-foreground">Individua i conflitti tra appelli dello stesso corso e anno o nella stessa aula, anche durante la compilazione.</p>
                </x-ui.card-content>
            </x-ui.card>
            <x-ui.card>
                <x-ui.card-content class="!p-6">
                    <x-ui.icon name="calendar-range" class="!size-7 text-accent-foreground" />
                    <h2 class="!mt-5 text-xl font-semibold">Consulta il calendario</h2>
                    <p class="!mb-0 text-muted-foreground">Ritrova gli appelli della sessione, raggruppati per giorno, con le informazioni disponibili per il tuo ruolo.</p>
                </x-ui.card-content>
            </x-ui.card>
        </section>
    </main>
    <footer class="border-t border-border py-6">
        <div class="app-container text-center text-sm text-muted-foreground">Coordinamento Esami &middot; Sistema di coordinamento delle date di esame</div>
    </footer>
</body>
</html>
