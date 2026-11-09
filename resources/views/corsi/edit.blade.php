@extends('layouts.master')

@section('title', 'Modifica corso di studio')
@section('heading', 'Modifica corso di studio')

@section('breadcrumb')
    <li class="ui-breadcrumb-item"><a href="{{ route('struttura.index') }}">Struttura didattica</a></li>
    <li class="ui-breadcrumb-item"><a href="{{ route('corsi.index') }}">Corsi di studio</a></li>
    <li class="ui-breadcrumb-item is-active" aria-current="page">Modifica</li>
@endsection

@section('content')
    <x-ui.card>
        <x-ui.card-content>
            <form method="POST" action="{{ route('corsi.update', $corso) }}" style="max-width: 32rem;">
                @csrf
                @method('PUT')
                @include('corsi._form')
            </form>
        </x-ui.card-content>
    </x-ui.card>
@endsection
