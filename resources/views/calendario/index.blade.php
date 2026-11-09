@extends('layouts.master')

@section('title', 'Calendario')
@section('heading', 'Calendario degli appelli')

@section('breadcrumb')
    <li class="ui-breadcrumb-item is-active" aria-current="page">Calendario</li>
@endsection

@section('content')

    {{-- Selettore della sessione --}}
    <form method="GET" action="{{ route('calendario.index') }}" class="ui-row ui-gutter-2 items-end mb-6" style="max-width: 32rem;">
        <div class="ui-col">
            <label for="sessione" class="ui-label">Sessione</label>
            <x-ui.select name="sessione" id="sessione"  onchange="this.form.submit()">
                @forelse ($sessioni as $s)
                    <option value="{{ $s->id }}" @selected($sessioneSelezionata && $sessioneSelezionata->id === $s->id)>
                        {{ $s->nome }} ({{ $s->data_inizio->format('d/m/Y') }} – {{ $s->data_fine->format('d/m/Y') }})
                    </option>
                @empty
                    <option value="">Nessuna sessione disponibile</option>
                @endforelse
            </x-ui.select>
        </div>
        <div class="ui-col-auto">
            <noscript><x-ui.button variant="outline-secondary" type="submit">Mostra</x-ui.button></noscript>
        </div>
    </form>

    @unless ($isAdmin)
        <p class="text-muted-foreground ui-small">
            <x-ui.icon name="info" /> Degli appelli altrui sono visibili solo data, corso, anno e fascia oraria occupati.
        </p>
    @endunless

    @if ($sessioneSelezionata === null)
        <x-ui.alert class="ui-alert-secondary">Nessuna sessione disponibile.</x-ui.alert>
    @elseif ($perData->isEmpty())
        <x-ui.alert class="ui-alert-secondary">Nessun appello presente in questa sessione.</x-ui.alert>
    @else
        @foreach ($perData as $data => $appelliDelGiorno)
            <x-ui.card class="mb-4">
                <x-ui.card-header class="font-semibold capitalize">
                    {{ \Illuminate\Support\Carbon::parse($data)->locale('it')->translatedFormat('l d/m/Y') }}
                </x-ui.card-header>
                <div class="ui-table-scroll">
                    <x-ui.table class="mb-0 align-middle">
                        <thead>
                            <tr>
                                <th style="width: 8rem;">Orario</th>
                                <th class="text-center" style="width: 6rem;">Anno</th>
                                <th>Corso</th>
                                <th>Insegnamento</th>
                                <th>Docente</th>
                                <th>Aula</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($appelliDelGiorno as $appello)
                                @php($visibile = $isAdmin || $appello->user_id === $userId || $insegnamentiIds->contains($appello->insegnamento_id))
                                @php($inConflitto = $idConflitto->contains($appello->id))
                                <tr class="{{ $inConflitto ? 'ui-row-danger' : '' }} {{ $visibile ? '' : 'text-muted-foreground' }}">
                                    <td>
                                        {{ \Illuminate\Support\Str::substr($appello->ora_inizio, 0, 5) }}&ndash;{{ \Illuminate\Support\Str::substr($appello->ora_fine, 0, 5) }}
                                        @if ($inConflitto)
                                            <x-ui.badge class="ui-tone-danger ml-1" title="In conflitto con un altro appello"><x-ui.icon name="alert-triangle" /> conflitto</x-ui.badge>
                                        @endif
                                        @if ($appello->data->lt($sessioneSelezionata->data_inizio))
                                            <x-ui.badge class="ui-tone-info ml-1" title="Data precedente l'inizio della sessione">preappello</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $appello->insegnamento->anno_frequenza }}°</td>
                                    <td>{{ $appello->insegnamento->corsoStudio->nome }}</td>
                                    @if ($visibile)
                                        <td>
                                            {{ $appello->insegnamento->nome }}
                                            @if (! $isAdmin && $appello->user_id === $userId)
                                                <x-ui.badge class="ui-tone-primary ml-1">tuo</x-ui.badge>
                                            @endif
                                        </td>
                                        <td>{{ $appello->docente->name }}</td>
                                        <td>{{ $appello->aula ?? '—' }}</td>
                                    @else
                                        <td colspan="3"><em>Occupato</em></td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </x-ui.table>
                </div>
            </x-ui.card>
        @endforeach
    @endif
@endsection
