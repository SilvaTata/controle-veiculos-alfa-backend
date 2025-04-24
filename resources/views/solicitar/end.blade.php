@extends('adminlte::page')
@extends('layouts.darkMode')
@section('content_header')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<link rel="stylesheet" href="{{ asset('css/custom-dark-mode.css') }}">


@section('content_header2')
@if(session('success'))
    <div class="alert alert-success" id="message" role="alert">
        {{ session('success') }}
    </div>
    @endif
@if(session('error'))
    <div class="alert alert-danger" id="message" role="alert">
        {{ session('error') }}
    </div>
@endif

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

@stop

@section('content')
<div class="content">
    <div class="card">
        <div class="card-body">
            
                <h3>Solicitação do {{ $solicitar->veiculo->marca->marca}} {{ $solicitar->veiculo->modelo->modelo}} - Finalizando</h3>
                <h4><strong>Devolução prevista:</strong> {{ \Carbon\Carbon::parse($solicitar->data_final)->format('d/m/Y') }}</h4>
                <form action="{{ route('solicitar.finalizar', $solicitar->id) }}" method="POST">
                    @csrf
                <div class="row mb-3">
                    <p><strong>Km do velocímetro:</strong></p>
                    <div class="col-md-6">
                        <input id="velocimetro_final" type="text" class="form-control @error('velocimetro_final') is-invalid @enderror" name="velocimetro_final" required>
                        @error('velocimetro_final')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <p><strong>Placa do veículo:</strong></p>
                    <div class="col-md-6" id="direcao">
                        <input id="placa_confirmar2" type="text" class="form-control @error('placa_confirmar2') is-invalid @enderror" name="placa_confirmar2" required>
                        @error('placa_confirmar2')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror   
                    </div>
                </div>

                <div class="row mb-3">
                    <p><strong>Observações:</strong></p>
                    <div class="col-md-6" id="direcao1">
                        <input id="obs_user" type="text" class="form-control @error('obs_user') is-invalid @enderror" name="obs_user" required>
                        @error('obs_user')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror   
                    </div>
                </div>

            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-info">Finalizar</button>
                {{-- <a href="{{ route('solicitar.finalizar', $solicitar->veiculo->id) }}" class="btn btn-info">Finalizar</a> --}}
            </div>
            </form>
    </div>
@endsection