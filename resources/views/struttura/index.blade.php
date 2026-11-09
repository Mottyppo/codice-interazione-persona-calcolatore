@extends('layouts.master')

@section('title', 'Struttura didattica')
@section('heading', 'Struttura didattica')

@section('breadcrumb')
    <li class="ui-breadcrumb-item is-active" aria-current="page">Struttura didattica</li>
@endsection

@section('content')
    <p class="text-muted-foreground">Gestisci corsi di studio, insegnamenti, sessioni d'esame e le impostazioni dei conflitti.</p>

    <div class="ui-row ui-gutter-4">
        <div class="ui-col-md-6 ui-col-lg-3">
            <x-ui.card class="h-full">
                <x-ui.card-content>
                    <h5 class="ui-card-title"><x-ui.icon name="graduation-cap" class="mr-1" /> Corsi di studio</h5>
                    <p class="ui-card-text text-muted-foreground">{{ $corsi }} corsi registrati.</p>
                    <x-ui.button variant="primary" size="sm" href="{{ route('corsi.index') }}">Gestisci</x-ui.button>
                </x-ui.card-content>
            </x-ui.card>
        </div>
        <div class="ui-col-md-6 ui-col-lg-3">
            <x-ui.card class="h-full">
                <x-ui.card-content>
                    <h5 class="ui-card-title"><x-ui.icon name="book-open" class="mr-1" /> Insegnamenti</h5>
                    <p class="ui-card-text text-muted-foreground">{{ $insegnamenti }} insegnamenti registrati.</p>
                    <x-ui.button variant="primary" size="sm" href="{{ route('insegnamenti.index') }}">Gestisci</x-ui.button>
                </x-ui.card-content>
            </x-ui.card>
        </div>
        <div class="ui-col-md-6 ui-col-lg-3">
            <x-ui.card class="h-full">
                <x-ui.card-content>
                    <h5 class="ui-card-title"><x-ui.icon name="calendar-range" class="mr-1" /> Sessioni</h5>
                    <p class="ui-card-text text-muted-foreground">{{ $sessioni }} sessioni registrate.</p>
                    <x-ui.button variant="primary" size="sm" href="{{ route('sessioni.index') }}">Gestisci</x-ui.button>
                </x-ui.card-content>
            </x-ui.card>
        </div>
        <div class="ui-col-md-6 ui-col-lg-3">
            <x-ui.card class="h-full">
                <x-ui.card-content>
                    <h5 class="ui-card-title"><x-ui.icon name="settings" class="mr-1" /> Conflitti</h5>
                    <p class="ui-card-text text-muted-foreground">Modalità attuale: <strong>{{ ucfirst($modalitaConflitto) }}</strong>.</p>
                    <x-ui.button variant="primary" size="sm" href="{{ route('configurazione.edit') }}">Configura</x-ui.button>
                </x-ui.card-content>
            </x-ui.card>
        </div>
    </div>
@endsection
