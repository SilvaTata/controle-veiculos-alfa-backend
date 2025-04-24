
@extends('layouts.darkMode')
@section('title', 'Home')
@section('content_header')
    <h1>Seja Bem-Vindo</h1>
@stop
@section('content')
<div class="container p-3">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ ('Bem-vindo')  }} </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('Você está logado como') }} {{auth()->user()->cargo_id == 1 ? 'Adm' : 'User' }}
                    <br>
                    {{ __('O que você deseja hoje?') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
