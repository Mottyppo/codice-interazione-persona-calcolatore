@extends('layouts.master')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')

    @if ($ruolo === 'amministratore')

        {{-- ============ Dashboard amministratore ============ --}}
        <div class="ui-row ui-gutter-3 mb-6">
            <div class="ui-col-6 ui-col-lg-3">
                <x-ui.card class="ui-tone-primary h-full">
                    <x-ui.card-content>
                        <div class="ui-stat-value font-bold">{{ $stats['corsi'] }}</div>
                        <div class="ui-small uppercase">Corsi di studio</div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>
            <div class="ui-col-6 ui-col-lg-3">
                <x-ui.card class="ui-tone-success h-full">
                    <x-ui.card-content>
                        <div class="ui-stat-value font-bold">{{ $stats['insegnamenti'] }}</div>
                        <div class="ui-small uppercase">Insegnamenti</div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>
            <div class="ui-col-6 ui-col-lg-3">
                <x-ui.card class="ui-tone-info h-full">
                    <x-ui.card-content>
                        <div class="ui-stat-value font-bold">{{ $stats['sessioni'] }}</div>
                        <div class="ui-small uppercase">Sessioni</div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>
            <div class="ui-col-6 ui-col-lg-3">
                <x-ui.card class="ui-tone-warning h-full">
                    <x-ui.card-content>
                        <div class="ui-stat-value font-bold">{{ $stats['appelli'] }}</div>
                        <div class="ui-small uppercase">Appelli</div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>
        </div>

        {{-- Monitoraggio scadenze: insegnamenti senza appello con finestra in scadenza o chiusa --}}
        @if ($segnalazioni->isNotEmpty())
            <x-ui.card class="ui-border-warning mb-6">
                <x-ui.card-header class="font-semibold ui-tone-warning ui-text-warning">
                    <x-ui.icon name="alert-triangle" /> Monitoraggio scadenze — appelli mancanti
                </x-ui.card-header>
                <x-ui.card-content>
                    <p class="ui-small text-muted-foreground mb-4">
                        Insegnamenti ancora privi di appello in sessioni la cui finestra di inserimento è in scadenza o già chiusa.
                    </p>
                    @foreach ($segnalazioni as $seg)
                        @php
                            $badge = $seg['stato'] === 'chiusa'
                                ? ['danger', 'finestra chiusa']
                                : ['warning', 'finestra in scadenza'];
                            $scadenza = $seg['sessione']->periodiInserimento->max('data_fine');
                        @endphp
                        <div class="mb-4">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <strong>{{ $seg['sessione']->nome }}</strong>
                                <x-ui.badge class="ui-tone-{{ $badge[0] }}">{{ $badge[1] }}</x-ui.badge>
                                @if ($scadenza)
                                    <span class="text-muted-foreground ui-small">inserimento entro il {{ $scadenza->format('d/m/Y') }}</span>
                                @endif
                            </div>
                            <ul class="mb-0">
                                @foreach ($seg['insegnamenti'] as $ins)
                                    <li>
                                        {{ $ins->nome }}
                                        <span class="text-muted-foreground">({{ $ins->corsoStudio->nome }}, {{ $ins->anno_frequenza }}° anno)</span>
                                        @if ($ins->docenti->isNotEmpty())
                                            — <span class="text-muted-foreground ui-small">{{ $ins->docenti->pluck('name')->join(', ') }}</span>
                                        @else
                                            — <x-ui.badge class="ui-tone-secondary">nessun docente associato</x-ui.badge>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </x-ui.card-content>
            </x-ui.card>
        @endif

        {{-- Conflitti già presenti: appelli che si sovrappongono, da risolvere --}}
        @if ($appelliInConflitto->isNotEmpty())
            <x-ui.card class="ui-border-danger mb-6">
                <x-ui.card-header class="font-semibold ui-tone-danger ui-text-danger">
                    <x-ui.icon name="alert-triangle" /> Conflitti rilevati ({{ $appelliInConflitto->count() }})
                </x-ui.card-header>
                <x-ui.card-content class="p-0">
                    <p class="ui-small text-muted-foreground m-4 mb-2">
                        Appelli che si sovrappongono per stesso corso e anno oppure per stessa aula. Aprili per modificarli e risolvere il conflitto.
                    </p>
                    <div class="ui-table-scroll">
                        <x-ui.table class="ui-table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Orario</th>
                                    <th>Insegnamento</th>
                                    <th>Docente</th>
                                    <th>Aula</th>
                                    <th class="text-end">Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($appelliInConflitto as $appello)
                                    <tr class="ui-row-danger">
                                        <td>{{ $appello->data->format('d/m/Y') }}</td>
                                        <td>{{ \Illuminate\Support\Str::substr($appello->ora_inizio, 0, 5) }}&ndash;{{ \Illuminate\Support\Str::substr($appello->ora_fine, 0, 5) }}</td>
                                        <td>
                                            {{ $appello->insegnamento->nome }}
                                            <span class="text-muted-foreground ui-small">({{ $appello->insegnamento->corsoStudio->nome }}, {{ $appello->insegnamento->anno_frequenza }}° anno)</span>
                                        </td>
                                        <td>{{ $appello->docente->name }}</td>
                                        <td>{{ $appello->aula ?? '—' }}</td>
                                        <td class="text-end">
                                            <x-ui.button variant="outline-danger" size="sm" href="{{ route('appelli.edit', $appello) }}">
                                                <x-ui.icon name="pencil" /> Risolvi
                                            </x-ui.button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </x-ui.table>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
        @endif

        <x-ui.card>
            <x-ui.card-header class="font-semibold">Prossimi appelli</x-ui.card-header>
            <x-ui.card-content class="p-0">
                @if ($prossimiAppelli->isEmpty())
                    <p class="text-muted-foreground m-4">Nessun appello in programma.</p>
                @else
                    <div class="ui-table-scroll">
                        <x-ui.table class="ui-table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Orario</th>
                                    <th>Insegnamento</th>
                                    <th>Docente</th>
                                    <th>Aula</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($prossimiAppelli as $appello)
                                    <tr class="{{ $idConflitto->contains($appello->id) ? 'ui-row-danger' : '' }}">
                                        <td>{{ $appello->data->format('d/m/Y') }}</td>
                                        <td>
                                            {{ \Illuminate\Support\Str::substr($appello->ora_inizio, 0, 5) }}&ndash;{{ \Illuminate\Support\Str::substr($appello->ora_fine, 0, 5) }}
                                            @if ($idConflitto->contains($appello->id))
                                                <x-ui.badge class="ui-tone-danger ml-1" title="In conflitto con un altro appello"><x-ui.icon name="alert-triangle" /> conflitto</x-ui.badge>
                                            @endif
                                        </td>
                                        <td>{{ $appello->insegnamento->nome }}</td>
                                        <td>{{ $appello->docente->name }}</td>
                                        <td>{{ $appello->aula ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </x-ui.table>
                    </div>
                @endif
            </x-ui.card-content>
        </x-ui.card>

    @else

        {{-- ============ Dashboard docente ============ --}}
        <x-ui.alert class="ui-alert-info">
            Benvenuto, <strong>{{ Auth::user()->name }}</strong>. Da qui puoi gestire i tuoi appelli d'esame.
        </x-ui.alert>

        {{-- Promemoria: insegnamenti del docente ancora senza appello nelle sessioni attive --}}
        @if ($daCompletare->isNotEmpty())
            <x-ui.card class="ui-border-warning mb-6">
                <x-ui.card-header class="font-semibold ui-tone-warning ui-text-warning">
                    <x-ui.icon name="alert-triangle" /> Insegnamenti da pianificare
                </x-ui.card-header>
                <x-ui.card-content>
                    <p class="ui-small text-muted-foreground mb-4">
                        Questi tuoi insegnamenti non hanno ancora un appello nella sessione indicata.
                    </p>
                    @foreach ($daCompletare as $seg)
                        @php
                            $mappa = [
                                'aperta' => ['secondary', 'inserimento aperto'],
                                'in_scadenza' => ['warning', 'in scadenza'],
                                'chiusa' => ['danger', 'finestra chiusa'],
                            ];
                            $badge = $mappa[$seg['stato']];
                            $scadenza = $seg['sessione']->periodiInserimento->max('data_fine');
                        @endphp
                        <div class="mb-4">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <strong>{{ $seg['sessione']->nome }}</strong>
                                <x-ui.badge class="ui-tone-{{ $badge[0] }}">{{ $badge[1] }}</x-ui.badge>
                                @if ($scadenza)
                                    <span class="text-muted-foreground ui-small">inserimento entro il {{ $scadenza->format('d/m/Y') }}</span>
                                @endif
                            </div>
                            <ul class="mb-0">
                                @foreach ($seg['insegnamenti'] as $ins)
                                    <li>
                                        {{ $ins->nome }}
                                        <span class="text-muted-foreground">({{ $ins->corsoStudio->nome }}, {{ $ins->anno_frequenza }}° anno)</span>
                                        @if ($seg['stato'] !== 'chiusa')
                                            <a href="{{ route('appelli.create', ['insegnamento' => $ins->id, 'sessione' => $seg['sessione']->id]) }}" class="ml-1">crea appello</a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </x-ui.card-content>
            </x-ui.card>
        @endif

        <div class="ui-row ui-gutter-4">
            <div class="ui-col-lg-6">
                <x-ui.card class="h-full">
                    <x-ui.card-header class="font-semibold">I miei insegnamenti</x-ui.card-header>
                    <ul class="ui-list">
                        @forelse ($insegnamenti as $insegnamento)
                            <li class="ui-list-item flex justify-between items-center">
                                <span>
                                    {{ $insegnamento->nome }}
                                    <small class="text-muted-foreground">({{ $insegnamento->corsoStudio->nome }})</small>
                                </span>
                                <x-ui.badge class="ui-tone-secondary">{{ $insegnamento->anno_frequenza }}° anno</x-ui.badge>
                            </li>
                        @empty
                            <li class="ui-list-item text-muted-foreground">Nessun insegnamento associato.</li>
                        @endforelse
                    </ul>
                </x-ui.card>
            </div>

            <div class="ui-col-lg-6">
                <x-ui.card class="h-full">
                    <x-ui.card-header class="font-semibold">I miei prossimi appelli</x-ui.card-header>
                    <ul class="ui-list">
                        @forelse ($mieiAppelli as $appello)
                            <li class="ui-list-item flex justify-between items-center {{ $idConflitto->contains($appello->id) ? 'ui-list-item-danger' : '' }}">
                                <span>
                                    {{ $appello->insegnamento->nome }}
                                    @if ($idConflitto->contains($appello->id))
                                        <x-ui.badge class="ui-tone-danger ml-1" title="In conflitto con un altro appello"><x-ui.icon name="alert-triangle" /> conflitto</x-ui.badge>
                                    @endif
                                    <small class="text-muted-foreground block">Aula: {{ $appello->aula ?? '—' }}</small>
                                </span>
                                <x-ui.badge class="ui-tone-primary">{{ $appello->data->format('d/m/Y') }}</x-ui.badge>
                            </li>
                        @empty
                            <li class="ui-list-item text-muted-foreground">Nessun appello in programma.</li>
                        @endforelse
                    </ul>
                </x-ui.card>
            </div>
        </div>

    @endif

@endsection
