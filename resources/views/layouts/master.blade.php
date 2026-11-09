<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Coordinamento Esami') · Coordinamento Esami</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="app-shell">
    @include('partials.navbar')
    <main class="app-container flex-grow py-6">
        @hasSection('breadcrumb')
            <nav aria-label="breadcrumb">
                <ol class="ui-breadcrumb">
                    <li class="ui-breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    @yield('breadcrumb')
                </ol>
            </nav>
        @endif
        @include('partials.flash')
        <div class="flex items-center justify-between mb-6">
            <h1 class="ui-heading !mb-0">@yield('heading', 'Coordinamento Esami')</h1>
            @yield('actions')
        </div>
        @yield('content')
    </main>
    <footer class="mt-auto border-t border-border bg-card py-4">
        <div class="app-container text-center ui-small text-muted-foreground">
            {{ config('app.name') }} &middot; Sistema di coordinamento delle date di esame
        </div>
    </footer>
    <div class="ui-dialog-shell" id="confirmModal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="confirmModalTitle" aria-describedby="confirmModalBody" aria-hidden="true" hidden>
        <div class="ui-dialog-position" data-dialog-backdrop>
            <div class="ui-dialog">
                <div class="ui-dialog-header">
                    <h5 class="ui-dialog-title" id="confirmModalTitle">Conferma operazione</h5>
                    <button type="button" class="ui-close" data-dialog-close aria-label="Chiudi"><x-ui.icon name="x" /></button>
                </div>
                <div class="ui-dialog-content" id="confirmModalBody">Confermi l'operazione?</div>
                <div class="ui-dialog-footer">
                    <x-ui.button type="button" variant="secondary" data-dialog-close>Annulla</x-ui.button>
                    <x-ui.button type="button" variant="danger" id="confirmModalOk">Conferma</x-ui.button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    @stack('scripts')
</body>
</html>
