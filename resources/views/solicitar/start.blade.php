@extends('layouts.darkMode')

@section('content_header')
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
                <h3>Solicitação do {{ $solicitar->veiculo->marca->marca}} {{ $solicitar->veiculo->modelo->modelo}} - Em progresso</h3>
                <h4><strong>Retirada prevista:</strong> {{ \Carbon\Carbon::parse($solicitar->data_inicial)->format('d/m/Y') }} às {{ \Carbon\Carbon::parse($solicitar->hora_inicial)->format('H\hi') }}</h4>
                <form action="{{ route('solicitar.prosseguir', $solicitar->id) }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <p><strong>Placa do veículo:</strong></p>
                        <div class="col-md-6" id="direcao">
                            <input id="placa_confirmar" type="text" class="form-control @error('placa_confirmar') não-é-válido @enderror" name="placa_confirmar" required>
                            @error('placa_confirmar')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror   
                        </div>
                    </div>
                    <div class="row mb-3">
                        <p><strong>Km do velocímetro: </strong></p>
                        <div class="col-md-6">
                            <input id="velocimetro_inicio" type="text" class="form-control @error('velocimetro_inicio') não-é-válido @enderror" name="velocimetro_inicio" required>
                            @error('velocimetro_inicio')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-info">Prosseguir</button>
                </div>
            </form>
            </div>
@endsection