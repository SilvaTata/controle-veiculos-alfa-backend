@extends('layouts.darkMode')
@section('content_header')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<link rel="stylesheet" href="{{ asset('css/custom-dark-mode.css') }}">

<script>   
        setTimeout(() => {
            const successMessage = document.getElementById("message");
            if (successMessage) {
                successMessage.style.transition = "opacity 0.5s ease";
                successMessage.style.opacity = "0";
                setTimeout(() => successMessage.remove(), 500);
            }
        }, 5000);
    </script>

    <h1>Veículos Disponíveis</h1>
    <form action="{{ route('solicitar.index') }}" method="GET">
        <input class="btn btn-novo" name="search" placeholder="Buscar veículo" value="{{ request('search') }}">
        <button type="submit" class="btn btn-novo">Buscar</button>
    </form>

    @if(session('error'))
    <div class="alert alert-danger" id="error-message" role="alert">
        {{ session('error') }}
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success" id="success-message" role="alert">
        {{ session('success') }}
    </div>
@endif
    <script>
    setTimeout(() => {
        const errorMessage = document.getElementById("error-message");
        const successMessage = document.getElementById("success-message");

        [errorMessage, successMessage].forEach((message) => {
            if (message) {
                message.style.transition = "opacity 0.5s ease";
                message.style.opacity = "0";
                setTimeout(() => message.remove(), 500);
            }
        });
    }, 5000);
</script>

@stop

@section('content')
   <div class="content">
    @if (session('sucess')) 
        <div class="alert alert-success" id="message" role="alert">
           {{ session('sucess') }}
        </div>
   </div>
   @endif
   <table class="table table-bordered table-hover">
       <thead>
           <tr>
                <th>Veículos:</th>
                <th>Placa:</th>
                <th>Reserva:</th>
                
        </thead>
        <tbody>
            @foreach ($veiculos as $veiculo)
            @if ($veiculo->funcionamento == 0)
            <tr>
                <td>{{ $veiculo->marca->marca}} - {{ $veiculo->modelo->modelo }}</td>
                <td>{{ $veiculo->placa}}</td>
                <td>
                    <a class="btn btn-info" href="{{ route('solicitar.create', $veiculo->id) }}">Solicitar</a>
                </td>
                    </tr>
                    @endif
                    @endforeach
        </tbody>
        @stop