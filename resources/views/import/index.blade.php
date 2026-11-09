@extends('layouts.master')

@section('title', 'Importa CSV')
@section('heading', 'Importa struttura didattica (CSV)')

@section('breadcrumb')
    <li class="ui-breadcrumb-item"><a href="{{ route('struttura.index') }}">Struttura didattica</a></li>
    <li class="ui-breadcrumb-item is-active" aria-current="page">Importa CSV</li>
@endsection

@section('content')

    {{-- Esito dell'import: righe con errori --}}
    @if (session('import_errori'))
        <x-ui.alert class="ui-alert-danger">
            <strong>Import non eseguito.</strong> Sono stati rilevati errori nelle righe seguenti; correggi il file e
            riprova (non è stato importato nulla).
            <x-ui.table class="ui-table-sm mt-2 mb-0">
                <thead>
                    <tr><th style="width: 6rem;">Riga</th><th>Problema</th></tr>
                </thead>
                <tbody>
                    @foreach (session('import_errori') as $errore)
                        <tr>
                            <td>{{ $errore['riga'] }}</td>
                            <td>{{ $errore['messaggio'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>
        </x-ui.alert>
    @endif

    {{-- Esito dell'import: riepilogo positivo --}}
    @if (session('import_riepilogo'))
        @php($r = session('import_riepilogo'))
        <x-ui.alert class="ui-alert-success">
            <strong>Import completato.</strong>
            <ul class="mb-0 mt-1">
                <li>Corsi creati: {{ $r['corsi_creati'] }}</li>
                <li>Insegnamenti creati: {{ $r['insegnamenti_creati'] }}</li>
                <li>Insegnamenti aggiornati: {{ $r['insegnamenti_aggiornati'] }}</li>
                <li>Associazioni docente-insegnamento: {{ $r['associazioni'] }}</li>
            </ul>
        </x-ui.alert>
    @endif

    <div class="ui-row ui-gutter-4">
        <div class="ui-col-lg-7">
            <x-ui.card>
                <x-ui.card-header class="font-semibold">Carica un file CSV</x-ui.card-header>
                <x-ui.card-content>
                    <form method="POST" action="{{ route('import.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="file" class="ui-label">File CSV</label>
                            <x-ui.input type="file" :invalid="$errors->has('file')"
                                id="file" name="file" accept=".csv,text/csv" required />
                            <x-ui.field-error for="file" />
                        </div>
                        <x-ui.button variant="primary" type="submit"><x-ui.icon name="upload" /> Importa</x-ui.button>
                    </form>
                </x-ui.card-content>
            </x-ui.card>
        </div>

        <div class="ui-col-lg-5">
            <x-ui.card>
                <x-ui.card-header class="font-semibold">Formato richiesto</x-ui.card-header>
                <x-ui.card-content>
                    <p class="mb-2">Il file deve avere una riga di intestazione con le colonne:</p>
                    <ul class="ui-small">
                        <li><code>corso</code> — nome del corso di studio</li>
                        <li><code>insegnamento</code> — nome dell'insegnamento</li>
                        <li><code>anno_frequenza</code> — numero da 1 a 3</li>
                        <li><code>docenti</code> (facoltativo) — email dei docenti già registrati, separate da <code>|</code></li>
                    </ul>
                    <p class="ui-small text-muted-foreground mb-4">
                        Separatore di colonna: virgola o punto e virgola (rilevato automaticamente). Corsi e insegnamenti
                        già presenti vengono aggiornati anziché duplicati.
                    </p>
                    <x-ui.button variant="outline-secondary" size="sm" href="{{ route('import.template') }}">
                        <x-ui.icon name="download" /> Scarica CSV di esempio
                    </x-ui.button>
                </x-ui.card-content>
            </x-ui.card>
        </div>
    </div>
@endsection
