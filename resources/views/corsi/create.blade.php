@extends('layouts.master')

@section('title', 'Nuovo corso di studio')
@section('heading', 'Nuovo corso di studio')

@section('breadcrumb')
    <li class="ui-breadcrumb-item"><a href="{{ route('struttura.index') }}">Struttura didattica</a></li>
    <li class="ui-breadcrumb-item"><a href="{{ route('corsi.index') }}">Corsi di studio</a></li>
    <li class="ui-breadcrumb-item is-active" aria-current="page">Nuovo</li>
@endsection

@section('content')
    <x-ui.card>
        <x-ui.card-content>
            <form method="POST" action="{{ route('corsi.store') }}" style="max-width: 32rem;">
                @csrf
                @include('corsi._form')
            </form>
        </x-ui.card-content>
    </x-ui.card>
@endsection
