@extends('layouts.darkMode')
@if (auth()->user()->cargo_id == 1)
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
<h1>Solicitações:</h1>
@endsection

@section('content')


@if(session('success'))
<div class="alert alert-success" id="message" role="alert">
    {{ session('success') }}
</div>
@endif
@if(session('danger'))
<div class="alert alert-danger" id="message" role="alert">
    {{ session('danger') }}
</div>
@endif

@if($solicitars->isNotEmpty())

<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th>Veículo:</th>
            <th>Retirada:</th>
            <th>Motivo:</th>
            <th>Situação:</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($solicitars as $solicitar)
        @if ($solicitar->situacao != 'Finalizada' && $solicitar->situacao != 'Recusada')
        <tr>
            <td>{{ $solicitar->veiculo->marca->marca }} {{ $solicitar->veiculo->modelo->modelo }} - {{ $solicitar->veiculo->placa }}</td>
            <td>Data: {{ \Carbon\Carbon::parse($solicitar->prev_data_inicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($solicitar->prev_data_final)->format('d/m/Y') }} <br> Hora: {{ $solicitar->prev_hora_inicio }}</td>
            <td> {{$solicitar->motivo}} </td>
            <td>
                <a href="{{ route('solicitar.ver', $solicitar->id) }}" class="btn btn-info">Ver</a>
            </td>
        </tr>
        @endif
        @endforeach
    </tbody>
</table>
@else
<h6>Não há solicitações realizadas.</h6>
@endif
@endsection

@else
@section('content_header')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<link rel="stylesheet" href="{{ asset('css/custom-dark-mode.css') }}">

<h1>Minhas Solicitações:</h1>
@endsection

@section('content')
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
@if(session('success'))
<div class="alert alert-success" id="message" role="alert">
    {{ session('success') }}
</div>
@endif

@if($solicitars->isNotEmpty())

<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th>Veículo:</th>
            <th>Retirada:</th>
            <th>Situação:</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($solicitars as $solicitar)
        @if ($solicitar->situacao != 'Finalizada' && $solicitar->situacao != 'Recusada')
        <tr>
            <td>{{ $solicitar->veiculo->marca->marca }} {{ $solicitar->veiculo->modelo->modelo }} - {{ $solicitar->veiculo->placa }}</td>
            <td>Data: {{ \Carbon\Carbon::parse($solicitar->data_inicial)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($solicitar->data_final)->format('d/m/Y') }} <br> Hora: {{ $solicitar->prev_hora_inicio }}</td>
            <td>
                <a href="{{ route('solicitar.ver', $solicitar->id) }}" class="btn btn-info">Ver</a>
            </td>
        </tr>
        @endif
        @endforeach
    </tbody>
</table>
@else
<h6>Não há solicitações realizadas.</h6>
@endif
@endsection
@endif