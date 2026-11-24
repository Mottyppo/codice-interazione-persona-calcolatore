<!DOCTYPE html>
<html lang="it" data-type="software">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Coordinamento Esami</title>
    <meta name="page-type" content="software">
    <meta name="site-kind" content="software">
    <meta name="description" content="Pianifica gli appelli universitari, verifica le sovrapposizioni e consulta il calendario delle sessioni d'esame.">
    <meta name="theme-color" content="#ffffff">
    <meta property="og:title" content="Coordinamento Esami">
    <meta property="og:description" content="Pianifica gli appelli universitari, verifica le sovrapposizioni e consulta il calendario delle sessioni d'esame.">
    <meta name="twitter:card" content="summary">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <script>document.documentElement.classList.add('m-enter');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="landing-page bg-background" id="inizio">
    <header class="nav landing-header">
        <a class="landing-skip" href="#contenuto">Salta al contenuto</a>
        <div class="wrap landing-wrap flex min-h-[4.5rem] items-center justify-between gap-3">
            <a href="#inizio" class="landing-brand">
                <x-application-logo class="size-9 shrink-0" />
                Coordinamento Esami
            </a>
            <x-ui.button :href="route('login')" class="landing-action">Accedi</x-ui.button>
        </div>
    </header>

    <main id="contenuto" tabindex="-1">
        <section class="hero landing-hero landing-dot-field" aria-labelledby="titolo-landing" data-enter>
            <div class="wrap landing-wrap">
                <div class="hero-text landing-intro">
                    <h1 id="titolo-landing">Coordina le date degli esami universitari</h1>
                    <p>Docenti e amministratori pianificano gli appelli e verificano le sovrapposizioni.</p>
                    <x-ui.button :href="route('login')" class="landing-action">
                        Accedi <x-ui.icon name="arrow-right" />
                    </x-ui.button>
                </div>

                <div class="app-frame frame landing-preview" role="group" aria-labelledby="titolo-anteprima" data-ui>
                    <div class="landing-preview-header">
                        <div class="flex items-center gap-2 font-semibold">
                            <x-ui.icon name="calendar-range" class="text-accent-foreground" />
                            <p id="titolo-anteprima">Calendario degli appelli</p>
                        </div>
                        <span class="text-sm text-muted-foreground">Dati di esempio</span>
                    </div>

                    <div class="landing-preview-body">
                        <div class="mb-5">
                            <span class="text-sm text-muted-foreground">Sessione</span>
                            <p class="font-semibold">Sessione estiva</p>
                        </div>

                        <div class="landing-agenda">
                            <p class="landing-agenda-day">Lunedì 15 giugno</p>
                            <ul class="landing-agenda-list">
                                <li>
                                    <span class="landing-agenda-time">09:00–11:00</span>
                                    <span class="landing-subject">Basi di Dati</span>
                                    <span class="landing-room">Aula A1</span>
                                </li>
                                <li class="landing-agenda-conflict">
                                    <span class="landing-agenda-time">10:00–12:00</span>
                                    <span class="landing-subject">Algoritmi</span>
                                    <span class="landing-room">Aula A1</span>
                                    <x-ui.badge class="ui-tone-danger">
                                        <x-ui.icon name="alert-triangle" /> Conflitto
                                    </x-ui.badge>
                                </li>
                            </ul>

                            <p class="landing-agenda-day">Martedì 16 giugno</p>
                            <ul class="landing-agenda-list">
                                <li>
                                    <span class="landing-agenda-time">14:00–16:00</span>
                                    <span class="landing-subject">Reti di Calcolatori</span>
                                    <span class="landing-room">Aula B2</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="landing-features" aria-labelledby="titolo-funzioni" data-scroll-moment>
            <div class="wrap landing-wrap">
                <h2 id="titolo-funzioni">Dalla pianificazione <span>al calendario</span></h2>

                <div class="landing-feature-grid">
                    <article class="landing-feature">
                        <div class="landing-feature-icon"><x-ui.icon name="calendar" /></div>
                        <h3>Pianifica gli appelli</h3>
                        <p>Imposta date, orari e aule per ogni insegnamento nelle finestre di inserimento della sessione.</p>
                        <div class="landing-feature-demo" data-ui>
                            <span>Finestra di inserimento</span>
                            <dl>
                                <div><dt>Apertura</dt><dd>3 giugno</dd></div>
                                <div><dt>Chiusura</dt><dd>12 giugno</dd></div>
                            </dl>
                        </div>
                    </article>
                    <article class="landing-feature">
                        <div class="landing-feature-icon"><x-ui.icon name="alert-triangle" /></div>
                        <h3>Verifica i conflitti</h3>
                        <p>Durante la compilazione, controlla le sovrapposizioni per corso e anno o per la stessa aula.</p>
                        <div class="landing-feature-demo" data-ui>
                            <span>Motivo del conflitto</span>
                            <strong><x-ui.icon name="alert-triangle" class="text-accent-foreground" /> Stessa aula · A1</strong>
                            <span class="tabular-nums">09:00–11:00 / 10:00–12:00</span>
                        </div>
                    </article>
                    <article class="landing-feature">
                        <div class="landing-feature-icon"><x-ui.icon name="calendar-range" /></div>
                        <h3>Consulta il calendario</h3>
                        <p>Scegli la sessione e ritrova gli appelli raggruppati per giorno, con i dettagli disponibili per il tuo ruolo.</p>
                        <div class="landing-feature-demo" data-ui>
                            <span>Sessione selezionata</span>
                            <strong>Sessione estiva</strong>
                            <span>Appelli per giorno</span>
                        </div>
                    </article>
                </div>

                <x-ui.button :href="route('login')" class="landing-action">Accedi <x-ui.icon name="arrow-right" /></x-ui.button>
            </div>
        </section>
    </main>

    <footer class="closing border-t border-border">
        <div class="wrap landing-wrap flex flex-wrap items-center gap-x-6 gap-y-1 py-6 text-sm">
            <p class="font-semibold">Coordinamento Esami</p>
            <p class="text-muted-foreground">Sistema di coordinamento delle date di esame</p>
        </div>
    </footer>
</body>
</html>
