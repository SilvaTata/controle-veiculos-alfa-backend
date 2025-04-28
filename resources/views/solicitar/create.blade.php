@extends('layouts.darkMode') {{-- Assume que seu layout base é este --}}

    @section('content_header')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
         <link rel="stylesheet" href="{{ asset('css/custom-dark-mode.css') }}">
        <h1>Solicitar Veículo</h1>
    @stop

    @section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header custom-card-header text-white">
                        <h5 class="mb-0">
                            <i class="fa fa-calendar-plus-o me-2"></i> Fazer Solicitação para:
                            <strong>{{ $veiculo->marca->marca }} {{ $veiculo->modelo->modelo }}</strong>
                            (Placa: {{ $veiculo->placa }} - Cor: {{ $veiculo->cor }})
                        </h5>
                    </div>

                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('solicitar.store') }}" id="create-solicitation-form">
                            @csrf
                            <input type="hidden" name="veiculo_id" value="{{ $veiculo->id }}">

                            {{-- Exibição de erros de validação --}}
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <fieldset class="mb-4">
                                <legend class="fs-6 fw-bold border-bottom pb-2 mb-3"> <i class="fa fa-clock-o me-2"></i> Período de Utilização Previsto</legend>
                                <div class="row g-3">
                                    {{-- Data de Retirada --}}
                                    <div class="col-md-6">
                                        <label for="prev_data_inicio" class="form-label">{{ __('Data de Retirada') }} <span class="text-danger">*</span></label>
                                        <input id="prev_data_inicio" type="date"
                                            class="form-control @error('prev_data_inicio') is-invalid @enderror"
                                            name="prev_data_inicio" value="{{ old('prev_data_inicio') }}" required
                                            min="{{ now()->format('Y-m-d') }}"> {{-- Data mínima é hoje --}}
                                        @error('prev_data_inicio')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="prev_hora_inicio" class="form-label">{{ __('Hora de Retirada') }} <span class="text-danger">*</span></label>
                                        <input id="prev_hora_inicio" type="time"
                                            class="form-control @error('prev_hora_inicio') is-invalid @enderror"
                                            name="prev_hora_inicio" value="{{ old('prev_hora_inicio') }}" required>
                                        @error('prev_hora_inicio')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="prev_data_final" class="form-label">{{ __('Data de Devolução') }} <span class="text-danger">*</span></label>
                                        <input id="prev_data_final" type="date"
                                            class="form-control @error('prev_data_final') is-invalid @enderror"
                                            name="prev_data_final" value="{{ old('prev_data_final') }}" required
                                            min="{{ old('prev_data_inicio', now()->format('Y-m-d')) }}"> {{-- Data mínima é a de início ou hoje --}}
                                        @error('prev_data_final')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="prev_hora_final" class="form-label">{{ __('Hora de Devolução') }} <span class="text-danger">*</span></label>
                                        <input id="prev_hora_final" type="time"
                                            class="form-control @error('prev_hora_final') is-invalid @enderror"
                                            name="prev_hora_final" value="{{ old('prev_hora_final') }}" required>
                                        @error('prev_hora_final')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="mb-4">
                                <legend class="fs-6 fw-bold border-bottom pb-2 mb-3"> <i class="fa fa-pencil-square-o me-2"></i> Motivo da Solicitação</legend>
                                <div class="col-12">
                                    <label for="motivo" class="form-label visually-hidden">{{ __('Motivo de utilização') }}</label> {{-- Label hidden pois a legend já descreve --}}
                                    <textarea id="motivo" class="form-control @error('motivo') is-invalid @enderror"
                                            name="motivo" rows="4" required
                                            placeholder="Descreva detalhadamente o propósito da utilização do veículo...">{{ old('motivo') }}</textarea>
                                    @error('motivo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </fieldset>

                            <div class="row mt-4 pt-3 border-top">
                                <div class="col-12 d-flex justify-content-end">
                                    <div class="d-flex" style="gap: 15px;">
                                        <a href="{{ route('veiculos.index') }}" class="btn btn-secondary">
                                            <i class="fa fa-times me-1"></i> Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fa fa-paper-plane me-1"></i> Enviar Solicitação
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const dataInicioInput = document.getElementById('prev_data_inicio');
        const dataFinalInput = document.getElementById('prev_data_final');

        if (dataInicioInput && dataFinalInput) {
            dataInicioInput.addEventListener('change', function() {
                dataFinalInput.min = this.value;
                if (dataFinalInput.value < this.value) {
                    dataFinalInput.value = '';
                }
            });

            if (dataInicioInput.value) {
                dataFinalInput.min = dataInicioInput.value;
            }
        }

        const allAlerts = document.querySelectorAll('.alert-dismissible');
        allAlerts.forEach(alertEl => {
            let timeout = 5000;
        });
    });
    </script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <style>
        .custom-card-header {
            background-color: #343a40;
            color: white;
        }
    </style>
    @endsection