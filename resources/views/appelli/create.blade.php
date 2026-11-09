@extends('layouts.master')

@section('title', 'Nuovo appello')
@section('heading', 'Nuovo appello')

@section('breadcrumb')
    <li class="ui-breadcrumb-item"><a href="{{ route('appelli.index') }}">Appelli</a></li>
    <li class="ui-breadcrumb-item is-active" aria-current="page">Nuovo</li>
@endsection

@section('content')
    <x-ui.card>
        <x-ui.card-content>
            <form method="POST" action="{{ route('appelli.store') }}">
                @csrf
                @include('appelli._form')
            </form>
        </x-ui.card-content>
    </x-ui.card>
@endsection
