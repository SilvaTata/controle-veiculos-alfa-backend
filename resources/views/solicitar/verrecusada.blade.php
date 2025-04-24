@extends('layouts.darkMode')


@if (auth()->user()->cargo_id == 1)
@section('content_header')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<link rel="stylesheet" href="{{ asset('css/custom-dark-mode.css') }}">
<h1>Solicitação recusada do {{$solicitar->user->name}}</h1>

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

@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success" id="message" role="alert">
    {{ session('success') }}
</div>
@endif
<div class="content">
    <div class="card">
        <div class="card-body">
            <h4>Veículo:</h4>
            <ul>
                <li>
                    <p><strong> {{ $solicitar->veiculo->marca->marca }} {{ $solicitar->veiculo->modelo->modelo }} - {{ $solicitar->veiculo->placa }}</strong></p>
                </li>
                <li>
                    <p><strong>Quilometragem atual:</strong> {{ $solicitar->veiculo->km_atual }} km </p>
                </li>
                <li>
                    <p><strong>Quilometragem até a revisão:</strong> {{ $solicitar->veiculo->km_revisao }} km </p>
                </li>
                <li>
                    <p><strong>Observações do veículo: </strong>{{ $solicitar->veiculo->observacao }}</p>
                </li>
            </ul>
            <h4>Detalhes da Solicitação Recusada</h4>
            <ul>
            <li>
                <p><strong>Responsável:</strong> {{ $solicitar->adm->name ?? 'Não informado' }}</p>
            </li>
            <li>
                <p><strong>Hora:</strong> {{ $solicitar->hora_recusa }} </p>
            </li>
            <li>
                <p><strong>Motivo: </strong> {{ $solicitar->motivo_recusa }} </p>
            </li>
            <li>
                <p><strong>Data:</strong> {{ \Carbon\Carbon::parse($solicitar->data_recusa)->format('d/m/y') }} </p>
            </li>
        </ul>
        </div>
        <div class="card-footer">
            <a class="btn btn-secundary" onclick="history.back()">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>
@endsection
@else
@section('content_header')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<link rel="stylesheet" href="{{ asset('css/custom-dark-mode.css') }}">
<h1>Minha solicitação recusada</h1>
@endsection

@section('content')
<div class="content">
    <div class="card">
        <div class="card-body">
            <h4>Veículo:</h4>
            <ul>
                <li>
                    <p><strong> {{ $solicitar->veiculo->marca->marca }} {{ $solicitar->veiculo->modelo->modelo }} - {{ $solicitar->veiculo->placa }}</strong></p>
                </li>
                <li>
                    <p><strong>Quilometragem atual:</strong> {{ $solicitar->veiculo->km_atual }} km </p>
                </li>
                <li>
                    <p><strong>Quilometragem até a revisão:</strong> {{ $solicitar->veiculo->km_revisao }} km </p>
                </li>
                <li>
                    <p><strong>Observações do veículo: </strong>{{ $solicitar->veiculo->observacao }}</p>
                </li>
            </ul>
            <h4>Detalhes da Solicitação Recusada</h4>
            <ul>
                <li>
                    <p><strong>Responsável:</strong> {{ $solicitar->responsavel->name ?? 'Não informado' }}</p>
                </li>
                <li>
                    <p><strong>Hora:</strong> {{ $solicitar->hora_recusado }} </p>
                </li>
                <li>
                    <p><strong>Motivo: </strong> {{ $solicitar->motivo_recusado }} </p>
                </li>
                <li>
                    <p><strong>Data:</strong> {{ \Carbon\Carbon::parse($solicitar->data_recusado)->format('d/m/y') }} </p>
                </li>
            </ul>
            
        </div>
        <div class="card-footer">
            <a class="btn btn-secundary" onclick="history.back()">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
        @endsection
        @endif