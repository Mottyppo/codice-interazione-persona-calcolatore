@extends('layouts.master')

@section('title', 'Nuovo insegnamento')
@section('heading', 'Nuovo insegnamento')

@section('breadcrumb')
    <li class="ui-breadcrumb-item"><a href="{{ route('struttura.index') }}">Struttura didattica</a></li>
    <li class="ui-breadcrumb-item"><a href="{{ route('insegnamenti.index') }}">Insegnamenti</a></li>
    <li class="ui-breadcrumb-item is-active" aria-current="page">Nuovo</li>
@endsection

@section('content')
    <x-ui.card>
        <x-ui.card-content>
            <form method="POST" action="{{ route('insegnamenti.store') }}">
                @csrf
                @include('insegnamenti._form')
            </form>
        </x-ui.card-content>
    </x-ui.card>
@endsection
