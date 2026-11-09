@extends('layouts.master')

@section('title', $isAdmin ? 'Appelli' : 'I miei appelli')
@section('heading', $isAdmin ? 'Tutti gli appelli' : 'I miei appelli')

@section('breadcrumb')
    <li class="ui-breadcrumb-item is-active" aria-current="page">Appelli</li>
@endsection

@section('actions')
    <x-ui.button variant="primary" href="{{ route('appelli.create') }}"><x-ui.icon name="plus" /> Nuovo appello</x-ui.button>
@endsection

@section('content')
    {{-- Filtro: tutti gli appelli oppure solo quelli in conflitto --}}
    @if ($totaleConflitti > 0)
        <div class="ui-button-group mb-4" role="group" aria-label="Filtro appelli">
            <x-ui.button variant="outline-secondary" href="{{ route('appelli.index') }}"
                class="{{ $soloConflitti ? '' : 'is-active' }}">Tutti</x-ui.button>
            <x-ui.button variant="outline-danger" href="{{ route('appelli.index', ['conflitti' => 1]) }}"
                class="{{ $soloConflitti ? 'is-active' : '' }}">
                <x-ui.icon name="alert-triangle" /> Solo conflitti ({{ $totaleConflitti }})
            </x-ui.button>
        </div>
    @endif

    <x-ui.card>
        <x-ui.card-content class="p-0">
            @if ($appelli->isEmpty())
                <p class="text-muted-foreground m-4">{{ $soloConflitti ? 'Nessun appello in conflitto.' : 'Nessun appello presente.' }}</p>
            @else
                <x-ui.table class="ui-table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Orario</th>
                            <th>Insegnamento</th>
                            <th class="text-center">Anno</th>
                            @if ($isAdmin)
                                <th>Docente</th>
                            @endif
                            <th>Sessione</th>
                            <th>Aula</th>
                            <th class="text-end">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($appelli as $appello)
                            @php($inConflitto = $idConflitto->contains($appello->id))
                            @php($preappello = $appello->data->lt($appello->sessione->data_inizio))
                            <tr class="{{ $inConflitto ? 'ui-row-danger' : '' }}">
                                <td>
                                    {{ $appello->data->format('d/m/Y') }}
                                    @if ($preappello)
                                        <x-ui.badge class="ui-tone-info ml-1" title="Data precedente l'inizio della sessione">preappello</x-ui.badge>
                                    @endif
                                </td>
                                <td>
                                    {{ \Illuminate\Support\Str::substr($appello->ora_inizio, 0, 5) }}&ndash;{{ \Illuminate\Support\Str::substr($appello->ora_fine, 0, 5) }}
                                    @if ($inConflitto)
                                        <x-ui.badge class="ui-tone-danger ml-1" title="In conflitto con un altro appello"><x-ui.icon name="alert-triangle" /> conflitto</x-ui.badge>
                                    @endif
                                </td>
                                <td>{{ $appello->insegnamento->nome }}</td>
                                <td class="text-center">{{ $appello->insegnamento->anno_frequenza }}°</td>
                                @if ($isAdmin)
                                    <td>{{ $appello->docente->name }}</td>
                                @endif
                                <td>{{ $appello->sessione->nome }}</td>
                                <td>{{ $appello->aula ?? '—' }}</td>
                                <td class="text-end">
                                    @if ($idModificabili->contains($appello->id))
                                        <x-ui.button variant="outline-secondary" size="sm" href="{{ route('appelli.edit', $appello) }}">
                                            <x-ui.icon name="pencil" /> Modifica
                                        </x-ui.button>
                                        <x-delete-form :action="route('appelli.destroy', $appello)"
                                            message="Eliminare questo appello di {{ $appello->insegnamento->nome }} del {{ $appello->data->format('d/m/Y') }}?" />
                                    @else
                                        <span class="text-muted-foreground ui-small" title="Finestra di inserimento chiusa">
                                            <x-ui.icon name="lock" /> finestra chiusa
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            @endif
        </x-ui.card-content>
    </x-ui.card>
@endsection
