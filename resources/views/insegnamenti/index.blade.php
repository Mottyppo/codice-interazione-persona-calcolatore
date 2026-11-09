@extends('layouts.master')

@section('title', 'Insegnamenti')
@section('heading', 'Insegnamenti')

@section('breadcrumb')
    <li class="ui-breadcrumb-item"><a href="{{ route('struttura.index') }}">Struttura didattica</a></li>
    <li class="ui-breadcrumb-item is-active" aria-current="page">Insegnamenti</li>
@endsection

@section('actions')
    <x-ui.button variant="primary" href="{{ route('insegnamenti.create') }}"><x-ui.icon name="plus" /> Nuovo insegnamento</x-ui.button>
@endsection

@section('content')
    @php
        $filtriAttivi = $filtri['corso'] || $filtri['anno'] || $filtri['q'];
    @endphp

    {{-- Barra dei filtri (invio in GET, auto-applicazione al cambio) --}}
    <form method="GET" action="{{ route('insegnamenti.index') }}" class="ui-row ui-gutter-2 items-end mb-4">
        <div class="ui-col-sm-4">
            <label for="corso" class="ui-label">Corso di studio</label>
            <x-ui.select name="corso" id="corso"  onchange="this.form.submit()">
                <option value="">Tutti i corsi</option>
                @foreach ($corsi as $corso)
                    <option value="{{ $corso->id }}" @selected((int) $filtri['corso'] === $corso->id)>{{ $corso->nome }}</option>
                @endforeach
            </x-ui.select>
        </div>
        <div class="ui-col-sm-2">
            <label for="anno" class="ui-label">Anno</label>
            <x-ui.select name="anno" id="anno"  onchange="this.form.submit()">
                <option value="">Tutti</option>
                @for ($a = 1; $a <= 3; $a++)
                    <option value="{{ $a }}" @selected((int) $filtri['anno'] === $a)>{{ $a }}° anno</option>
                @endfor
            </x-ui.select>
        </div>
        <div class="ui-col-sm-4">
            <label for="q" class="ui-label">Cerca per nome</label>
            <x-ui.input type="text" name="q" id="q"  value="{{ $filtri['q'] }}"
                placeholder="Nome insegnamento…" onchange="this.form.submit()" />
        </div>
        <div class="ui-col-sm-2 flex gap-2">
            <noscript><x-ui.button variant="outline-secondary" type="submit" class="w-full">Filtra</x-ui.button></noscript>
            @if ($filtriAttivi)
                <x-ui.button variant="outline-secondary" href="{{ route('insegnamenti.index') }}"  title="Azzera filtri"><x-ui.icon name="x" /></x-ui.button>
            @endif
        </div>
    </form>

    <x-ui.card>
        <x-ui.card-content class="p-0">
            @if ($insegnamenti->isEmpty())
                <p class="text-muted-foreground m-4">
                    {{ $filtriAttivi ? 'Nessun insegnamento corrisponde ai filtri.' : 'Nessun insegnamento registrato.' }}
                </p>
            @else
                <x-ui.table class="ui-table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th class="text-center">Anno</th>
                            <th>Docenti</th>
                            <th class="text-end">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Raggruppamento per corso di studio (gruppi in ordine alfabetico) --}}
                        @php
                            $gruppi = $insegnamenti->groupBy('corsoStudio.nome')->sortKeys();
                        @endphp
                        @foreach ($gruppi as $nomeCorso => $gruppo)
                            <tr class="ui-row-light">
                                <th colspan="4">
                                    <x-ui.icon name="graduation-cap" /> {{ $nomeCorso }}
                                    <x-ui.badge class="ui-tone-secondary ml-1">{{ $gruppo->count() }}</x-ui.badge>
                                </th>
                            </tr>
                            @foreach ($gruppo as $insegnamento)
                                <tr>
                                    <td>{{ $insegnamento->nome }}</td>
                                    <td class="text-center">{{ $insegnamento->anno_frequenza }}°</td>
                                    <td>
                                        @forelse ($insegnamento->docenti as $docente)
                                            <x-ui.badge class="ui-tone-light border">{{ $docente->name }}</x-ui.badge>
                                        @empty
                                            <span class="text-muted-foreground">—</span>
                                        @endforelse
                                    </td>
                                    <td class="text-end">
                                        <x-ui.button variant="outline-secondary" size="sm" href="{{ route('insegnamenti.edit', $insegnamento) }}">
                                            <x-ui.icon name="pencil" /> Modifica
                                        </x-ui.button>
                                        @php
                                            $msg = "Eliminare l'insegnamento «{$insegnamento->nome}»?";
                                            if ($insegnamento->appelli_count > 0) {
                                                $msg .= ' Verranno eliminati anche ' . $insegnamento->appelli_count . ' '
                                                    . ($insegnamento->appelli_count === 1 ? 'appello' : 'appelli') . '.';
                                            }
                                        @endphp
                                        <x-delete-form :action="route('insegnamenti.destroy', $insegnamento)" :message="$msg" />
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </x-ui.table>
            @endif
        </x-ui.card-content>
    </x-ui.card>
@endsection
