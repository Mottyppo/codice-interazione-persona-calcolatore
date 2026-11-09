@extends('layouts.master')

@section('title', 'Corsi di studio')
@section('heading', 'Corsi di studio')

@section('breadcrumb')
    <li class="ui-breadcrumb-item"><a href="{{ route('struttura.index') }}">Struttura didattica</a></li>
    <li class="ui-breadcrumb-item is-active" aria-current="page">Corsi di studio</li>
@endsection

@section('actions')
    <x-ui.button variant="primary" href="{{ route('corsi.create') }}"><x-ui.icon name="plus" /> Nuovo corso</x-ui.button>
@endsection

@section('content')
    <x-ui.card>
        <x-ui.card-content class="p-0">
            @if ($corsi->isEmpty())
                <p class="text-muted-foreground m-4">Nessun corso di studio registrato.</p>
            @else
                <x-ui.table class="ui-table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th class="text-center">Insegnamenti</th>
                            <th class="text-end">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($corsi as $corso)
                            <tr>
                                <td>{{ $corso->nome }}</td>
                                <td class="text-center">{{ $corso->insegnamenti_count }}</td>
                                <td class="text-end">
                                    <x-ui.button variant="outline-secondary" size="sm" href="{{ route('corsi.edit', $corso) }}">
                                        <x-ui.icon name="pencil" /> Modifica
                                    </x-ui.button>
                                    @php
                                        $parti = [];
                                        if ($corso->insegnamenti_count > 0) {
                                            $parti[] = $corso->insegnamenti_count . ' ' . ($corso->insegnamenti_count === 1 ? 'insegnamento' : 'insegnamenti');
                                        }
                                        if ($corso->appelli_count > 0) {
                                            $parti[] = $corso->appelli_count . ' ' . ($corso->appelli_count === 1 ? 'appello' : 'appelli');
                                        }
                                        $msg = "Eliminare il corso «{$corso->nome}»?";
                                        if ($parti) {
                                            $msg .= ' Verranno eliminati anche ' . implode(' e ', $parti) . '.';
                                        }
                                    @endphp
                                    <x-delete-form :action="route('corsi.destroy', $corso)" :message="$msg" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            @endif
        </x-ui.card-content>
    </x-ui.card>
@endsection
