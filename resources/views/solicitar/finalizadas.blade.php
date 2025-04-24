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

@if ($solicitars->isNotEmpty())
@if(auth()->user()->cargo_id == 1)
    <h1>Solicitações Finalizadas:</h1>
    <div class="d-flex justify-content-end mb-3">
    <a href="{{ route('exportar.todas.excel') }}" class="btn btn-success">
        <i class="fa fa-file-excel-o"></i> Exportar Todas em Excel
    </a>
    </div>
@else 
    <h1>Minhas Solicitações Finalizadas:</h1>
@endif
@endsection

@section('content')
@if (session('success'))
    <div class="alert alert-success" id="message" role="alert">
        {{ session('success') }}
    </div>
@endif

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
                @if ($solicitar->situacao == 'concluída')
                    <tr>
                        <td>{{ $solicitar->veiculo->marca->marca }} {{ $solicitar->veiculo->modelo->modelo }} - {{ $solicitar->veiculo->placa }}</td>
                        <td>Data: {{ \Carbon\Carbon::parse($solicitar->historico->data_inicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($solicitar->historico->data_final)->format('d/m/Y') }} <br> Hora: {{ $solicitar->historico->hora_inicio }} a  {{ $solicitar->historico->hora_final }} </td>
                        <td>{{ $solicitar->motivo }}</td>
                        <td>
                            <a href="{{ route('gerar.pdf', $solicitar->id) }}" class="btn btn-info">Gerar PDF</a>
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