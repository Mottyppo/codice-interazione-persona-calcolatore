@extends('layouts.master')

@section('title', $sessione->nome)
@section('heading', $sessione->nome)

@section('breadcrumb')
    <li class="ui-breadcrumb-item"><a href="{{ route('struttura.index') }}">Struttura didattica</a></li>
    <li class="ui-breadcrumb-item"><a href="{{ route('sessioni.index') }}">Sessioni</a></li>
    <li class="ui-breadcrumb-item is-active" aria-current="page">{{ $sessione->nome }}</li>
@endsection

@section('content')
    <x-ui.card class="mb-6">
        <x-ui.card-content>
            <p class="mb-1"><strong>Periodo della sessione:</strong>
                {{ $sessione->data_inizio->format('d/m/Y') }} &ndash; {{ $sessione->data_fine->format('d/m/Y') }}</p>
            <p class="text-muted-foreground mb-0">
                Le finestre di inserimento indicano i giorni in cui i docenti possono creare appelli per questa sessione.
            </p>
        </x-ui.card-content>
    </x-ui.card>

    <x-ui.card class="mb-6">
        <x-ui.card-header class="font-semibold">Finestre di inserimento</x-ui.card-header>
        <x-ui.card-content class="p-0">
            @if ($sessione->periodiInserimento->isEmpty())
                <p class="text-muted-foreground m-4">Nessuna finestra di inserimento definita.</p>
            @else
                <x-ui.table class="align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Dal</th>
                            <th>Al</th>
                            <th class="text-end">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sessione->periodiInserimento as $periodo)
                            <tr>
                                <td>{{ $periodo->data_inizio->format('d/m/Y') }}</td>
                                <td>{{ $periodo->data_fine->format('d/m/Y') }}</td>
                                <td class="text-end">
                                    <x-delete-form :action="route('periodi.destroy', [$sessione, $periodo])"
                                        message="Eliminare questa finestra di inserimento?" label="" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            @endif
        </x-ui.card-content>
    </x-ui.card>

    <x-ui.card>
        <x-ui.card-header class="font-semibold">Aggiungi una finestra di inserimento</x-ui.card-header>
        <x-ui.card-content>
            <form method="POST" action="{{ route('periodi.store', $sessione) }}">
                @csrf
                <div class="ui-row ui-gutter-3 items-end">
                    <div class="ui-col-md-5">
                        <label for="data_inizio" class="ui-label">Dal</label>
                        <x-ui.input type="date" :invalid="$errors->has('data_inizio')" aria-describedby="finestra-help" id="data_inizio" name="data_inizio"
                            value="{{ \App\Support\FormValue::old('data_inizio') }}"
                            max="{{ $sessione->data_fine->format('Y-m-d') }}" required />
                        <x-ui.field-error for="data_inizio" />
                    </div>
                    <div class="ui-col-md-5">
                        <label for="data_fine" class="ui-label">Al</label>
                        <x-ui.input type="date" :invalid="$errors->has('data_fine')" aria-describedby="finestra-help" id="data_fine" name="data_fine"
                            value="{{ \App\Support\FormValue::old('data_fine') }}"
                            max="{{ $sessione->data_fine->format('Y-m-d') }}" data-date-after-or-equal="data_inizio" required />
                        <x-ui.field-error for="data_fine" />
                    </div>
                    <div class="ui-col-md-2">
                        <x-ui.button variant="primary" type="submit" class="w-full">Aggiungi</x-ui.button>
                    </div>
                </div>
                <p id="finestra-help" class="ui-field-help mb-0 mt-2">La finestra può aprirsi anche prima dell'inizio della sessione
                    (per pubblicare le date in anticipo), ma deve terminare entro la fine
                    ({{ $sessione->data_fine->format('d/m/Y') }}).</p>
            </form>
        </x-ui.card-content>
    </x-ui.card>
@endsection
