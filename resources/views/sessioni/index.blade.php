@extends('layouts.master')

@section('title', 'Sessioni')
@section('heading', "Sessioni d'esame")

@section('breadcrumb')
    <li class="ui-breadcrumb-item"><a href="{{ route('struttura.index') }}">Struttura didattica</a></li>
    <li class="ui-breadcrumb-item is-active" aria-current="page">Sessioni</li>
@endsection

@section('actions')
    <x-ui.button variant="primary" href="{{ route('sessioni.create') }}"><x-ui.icon name="plus" /> Nuova sessione</x-ui.button>
@endsection

@section('content')
    <x-ui.card>
        <x-ui.card-content class="p-0">
            @if ($sessioni->isEmpty())
                <p class="text-muted-foreground m-4">Nessuna sessione registrata.</p>
            @else
                <x-ui.table class="ui-table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Periodo</th>
                            <th class="text-center">Finestre inserimento</th>
                            <th class="text-center">Appelli</th>
                            <th class="text-end">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sessioni as $sessione)
                            <tr>
                                <td>{{ $sessione->nome }}</td>
                                <td>{{ $sessione->data_inizio->format('d/m/Y') }} &ndash; {{ $sessione->data_fine->format('d/m/Y') }}</td>
                                <td class="text-center">{{ $sessione->periodi_inserimento_count }}</td>
                                <td class="text-center">{{ $sessione->appelli_count }}</td>
                                <td class="text-end">
                                    <x-ui.button variant="outline-primary" size="sm" href="{{ route('sessioni.show', $sessione) }}">
                                        <x-ui.icon name="eye" /> Dettagli
                                    </x-ui.button>
                                    <x-ui.button variant="outline-secondary" size="sm" href="{{ route('sessioni.edit', $sessione) }}">
                                        <x-ui.icon name="pencil" /> Modifica
                                    </x-ui.button>
                                    @php
                                        $parti = [];
                                        if ($sessione->periodi_inserimento_count > 0) {
                                            $parti[] = $sessione->periodi_inserimento_count . ' '
                                                . ($sessione->periodi_inserimento_count === 1 ? 'finestra di inserimento' : 'finestre di inserimento');
                                        }
                                        if ($sessione->appelli_count > 0) {
                                            $parti[] = $sessione->appelli_count . ' ' . ($sessione->appelli_count === 1 ? 'appello' : 'appelli');
                                        }
                                        $msg = "Eliminare la sessione «{$sessione->nome}»?";
                                        if ($parti) {
                                            $msg .= ' Verranno eliminati anche ' . implode(' e ', $parti) . '.';
                                        }
                                    @endphp
                                    <x-delete-form :action="route('sessioni.destroy', $sessione)" :message="$msg" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            @endif
        </x-ui.card-content>
    </x-ui.card>
@endsection
