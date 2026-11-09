@extends('layouts.master')

@section('title', 'Modifica sessione')
@section('heading', 'Modifica sessione')

@section('breadcrumb')
    <li class="ui-breadcrumb-item"><a href="{{ route('struttura.index') }}">Struttura didattica</a></li>
    <li class="ui-breadcrumb-item"><a href="{{ route('sessioni.index') }}">Sessioni</a></li>
    <li class="ui-breadcrumb-item is-active" aria-current="page">Modifica</li>
@endsection

@section('content')
    <x-ui.card>
        <x-ui.card-content>
            <form method="POST" action="{{ route('sessioni.update', $sessione) }}" style="max-width: 36rem;">
                @csrf
                @method('PUT')
                @include('sessioni._form')
            </form>
        </x-ui.card-content>
    </x-ui.card>
@endsection
