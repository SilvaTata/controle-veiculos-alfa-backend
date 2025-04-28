@extends('layouts.darkMode')

@section('content_header')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/custom-dark-mode.css') }}">
    <h1>Cadastrar Novo Veículo</h1>
@stop

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12">
            <div class="card shadow-sm">
                <div class="card-header custom-card-header text-white">
                    <h5 class="mb-0"> <i class="fa fa-car me-2"></i> Informações do Veículo</h5>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('veiculos.store') }}" id="create-vehicle-form">
                        @csrf

                        {{-- Exibição de erros e sucesso --}}
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif
                        @if (session('success'))
                             <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h6 class="alert-heading">Por favor, corrija os erros abaixo:</h6>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Seção: Marca e Modelo --}}
                        <fieldset class="mb-4">
                            <legend class="fs-6 fw-bold border-bottom pb-2 mb-3">Marca e Modelo</legend>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label for="marca_id" class="form-label mb-0">{{ __('Marca') }} <span class="text-danger">*</span></label>
                                        <button type="button" class="btn btn-outline-primary btn-sm py-0 px-1" data-bs-toggle="modal" data-bs-target="#modalNovaMarca" title="Cadastrar nova marca">
                                            <i class="fa fa-plus"></i> Nova
                                        </button>
                                    </div>
                                    <select id="marca_id" class="form-select @error('marca_id') is-invalid @enderror" name="marca_id" required>
                                        <option value="" disabled {{ old('marca_id') ? '' : 'selected' }}>Selecione...</option>
                                        @foreach($marcas as $marca)
                                            <option value="{{ $marca->id }}" {{ old('marca_id') == $marca->id ? 'selected' : '' }}>
                                                {{ $marca->marca }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('marca_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                     <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label for="modelo_id" class="form-label mb-0">{{ __('Modelo') }} <span class="text-danger">*</span></label>
                                        {{-- Botão para abrir Modal de Modelo --}}
                                        <button type="button" class="btn btn-outline-primary btn-sm py-0 px-1" data-bs-toggle="modal" data-bs-target="#modalNovoModelo" title="Cadastrar novo modelo">
                                            <i class="fa fa-plus"></i> Novo
                                        </button>
                                    </div>
                                    <select id="modelo_id" class="form-select @error('modelo_id') is-invalid @enderror" name="modelo_id" required>
                                        <option value="" disabled {{ old('modelo_id') ? '' : 'selected' }}>Selecione...</option>
                                        @foreach($modelos as $modelo)
                                            <option value="{{ $modelo->id }}" {{ old('modelo_id') == $modelo->id ? 'selected' : '' }}>
                                                {{ $modelo->modelo }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('modelo_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </fieldset>

                        {{-- Seção: Identificação --}}
                        <fieldset class="mb-4">
                             <legend class="fs-6 fw-bold border-bottom pb-2 mb-3">Identificação</legend>
                             <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="placa" class="form-label">{{ __('Placa') }} <span class="text-danger">*</span></label>
                                    <input id="placa" type="text" class="form-control @error('placa') is-invalid @enderror" name="placa" value="{{ old('placa') }}" required maxlength="10" placeholder="AAA-0A00" style="text-transform: uppercase;">
                                    @error('placa')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-8">
                                    <label for="chassi" class="form-label">{{ __('Chassi') }} <span class="text-danger">*</span></label>
                                    <input id="chassi" type="text" class="form-control @error('chassi') is-invalid @enderror" name="chassi" value="{{ old('chassi') }}" required maxlength="17" placeholder="17 caracteres alfanuméricos" style="text-transform: uppercase;">
                                    @error('chassi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </fieldset>

                        {{-- Seção: Especificações --}}
                        <fieldset class="mb-4">
                            <legend class="fs-6 fw-bold border-bottom pb-2 mb-3">Especificações</legend>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="ano" class="form-label">{{ __('Ano Fabricação') }} <span class="text-danger">*</span></label>
                                    <input id="ano" type="number" class="form-control @error('ano') is-invalid @enderror" name="ano" value="{{ old('ano') }}" required min="1950" max="{{ date('Y') + 1 }}" placeholder="YYYY">
                                    @error('ano')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="cor" class="form-label">{{ __('Cor Predominante') }} <span class="text-danger">*</span></label>
                                    <input id="cor" type="text" class="form-control @error('cor') is-invalid @enderror" name="cor" value="{{ old('cor') }}" required maxlength="30">
                                    @error('cor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="capacidade" class="form-label">{{ __('Capacidade') }} <span class="text-danger">*</span></label>
                                    <input id="capacidade" type="number" class="form-control @error('capacidade') is-invalid @enderror" name="capacidade" value="{{ old('capacidade') }}" required min="1" placeholder="Nº de pessoas">
                                    @error('capacidade')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </fieldset>

                        {{-- Seção: Quilometragem e Status --}}
                        <fieldset class="mb-4">
                            <legend class="fs-6 fw-bold border-bottom pb-2 mb-3">Quilometragem e Status</legend>
                             <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="km_atual" class="form-label">{{ __('Km Atual') }} <span class="text-danger">*</span></label>
                                    <input id="km_atual" type="number" class="form-control @error('km_atual') is-invalid @enderror" name="km_atual" value="{{ old('km_atual', 0) }}" required min="0">
                                    @error('km_atual')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="km_revisao" class="form-label">{{ __('Próx. Revisão (Km)') }} <span class="text-danger">*</span></label>
                                    <input id="km_revisao" type="number" class="form-control @error('km_revisao') is-invalid @enderror" name="km_revisao" value="{{ old('km_revisao', 10000) }}" required min="0" title="Intervalo em KM para a próxima revisão preventiva">
                                    @error('km_revisao')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="status_veiculo" class="form-label">Status Inicial <span class="text-danger">*</span></label>
                                    <select name="status_veiculo" id="status_veiculo" class="form-select @error('status_veiculo') is-invalid @enderror" required>
                                        <option value="" disabled {{ old('status_veiculo') ? '' : 'selected' }}>Selecione...</option>
                                        @foreach(['disponível', 'manutenção', 'reservado', 'em uso'] as $status)
                                            <option value="{{ $status }}" {{ old('status_veiculo', 'disponível') == $status ? 'selected' : '' }}>
                                                {{ ucfirst($status) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status_veiculo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </fieldset>

                        {{-- Seção: Observações --}}
                        <fieldset class="mb-4">
                            <legend class="fs-6 fw-bold border-bottom pb-2 mb-3">Observações</legend>
                            <div class="col-12">
                                <textarea id="obs_veiculo" class="form-control @error('obs_veiculo') is-invalid @enderror" name="obs_veiculo" rows="3" placeholder="Detalhes adicionais, avarias, etc...">{{ old('obs_veiculo') }}</textarea>
                                @error('obs_veiculo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </fieldset>

                        {{-- Seção: Ações --}}
                        <div class="row mt-4 pt-3 border-top">
                            <div class="col-12 d-flex justify-content-end">
                                <a href="{{ route('veiculos.index') }}" class="btn btn-secondary me-2">
                                    <i class="fa fa-times me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-save me-1"></i> Cadastrar Veículo
                                </button>
                            </div>
                        </div>
                    </form> {{-- Fim do Formulário Principal --}}
                </div> {{-- Fim do card-body --}}
            </div> {{-- Fim do card --}}
        </div> {{-- Fim da Coluna --}}
    </div> {{-- Fim da Row --}}
</div> {{-- Fim do Container --}}

{{-- Modals (Estrutura Inalterada) --}}
<div class="modal fade" id="modalNovaMarca" tabindex="-1" aria-labelledby="modalNovaMarcaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form method="POST" action="{{ route('marcas.store') }}" id="create-marca-form">
        @csrf
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalNovaMarcaLabel">Cadastrar Nova Marca</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="nomeMarca" class="form-label">Nome da Marca <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="nomeMarca" name="nome" required placeholder="Ex: Chevrolet">
            </div>
          </div>
          <div class="modal-footer">
             <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
             <button type="submit" class="btn btn-primary"> <i class="fa fa-save me-1"></i> Salvar Marca</button>
          </div>
        </div>
      </form>
    </div>
</div>

<div class="modal fade" id="modalNovoModelo" tabindex="-1" aria-labelledby="modalNovoModeloLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form method="POST" action="{{ route('modelos.store') }}" id="create-modelo-form">
        @csrf
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalNovoModeloLabel">Cadastrar Novo Modelo</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="nomeModelo" class="form-label">Nome do Modelo <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="nomeModelo" name="nome" required placeholder="Ex: Onix">
            </div>
          </div>
          <div class="modal-footer">
             <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
             <button type="submit" class="btn btn-primary"> <i class="fa fa-save me-1"></i> Salvar Modelo</button>
          </div>
        </div>
      </form>
    </div>
</div>

{{-- Estilos (Inalterado) --}}
<style>
    .custom-card-header {
        background-color: #2c3e50;
    }
    .btn-outline-primary.btn-sm.py-0 {
       line-height: 1.2;
       padding-top: 0.1rem;
       padding-bottom: 0.1rem;
    }
</style>

{{-- Script de Alerta (Inalterado) --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const allAlerts = document.querySelectorAll('.alert-dismissible');
        allAlerts.forEach(alertEl => {
            let timeout = alertEl.querySelector('ul') ? 7000 : 5000;
            setTimeout(() => {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
                if(bsAlert) bsAlert.close();
            }, timeout);
        });
    });
</script>

{{-- Script Bootstrap JS (Movido para DENTRO da Seção) --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

@endsection {{-- Fim da Seção de Conteúdo --}}