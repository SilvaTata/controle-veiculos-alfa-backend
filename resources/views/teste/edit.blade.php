@extends('layouts.darkMode') {{-- Mantém o layout base --}}

@section('content_header')
    {{-- Links CSS e Título copiados/adaptados do veiculos.edit --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/custom-dark-mode.css') }}">
    {{-- Título da página atualizado --}}
    <h1>Editar Usuário: {{ $user->name }}</h1>
@stop

@section('content')
<div class="container py-4"> {{-- Adicionado py-4 --}}
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12"> {{-- Coluna mais larga como em veiculos --}}
            <div class="card shadow-sm"> {{-- Adicionado shadow-sm --}}
                <div class="card-header custom-card-header text-white"> {{-- Estilo de header de veiculos --}}
                    {{-- Cabeçalho do Card atualizado --}}
                    <h5 class="mb-0"> <i class="fa fa-pencil me-2"></i> Editar Informações do Usuário</h5>
                </div>

                <div class="card-body p-4"> {{-- Adicionado p-4 --}}
                    {{-- Mantida a rota e ID do form --}}
                    <form method="POST" action="{{ route('teste.update', $user->id) }}" id="edit-user-form">
                        @csrf
                        @method('PUT') {{-- Essencial para update --}}

                        {{-- Bloco de exibição de erros e sucesso (padrão veiculos.edit) --}}
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        @if (session('success') || session('sucess')) {{-- Captura ambos os typos --}}
                             <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') ?? session('sucess') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- Seção: Dados Pessoais e Contato (estrutura fieldset) --}}
                        <fieldset class="mb-4">
                            <legend class="fs-6 fw-bold border-bottom pb-2 mb-3">Dados Pessoais e Contato</legend>
                            <div class="row g-3"> {{-- row com gap g-3 --}}

                                {{-- Campo Nome (mantém name="name") --}}
                                <div class="col-md-6">
                                    <label for="name" class="form-label">{{ __('Nome Completo') }} <span class="text-danger">*</span></label>
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" autofocus>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div> {{-- div invalid-feedback --}}
                                    @enderror
                                </div>

                                {{-- Campo Email (mantém name="email") --}}
                                <div class="col-md-6">
                                    <label for="email" class="form-label">{{ __('Endereço de E-mail') }} <span class="text-danger">*</span></label>
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Campo CPF (mantém name="cpf") --}}
                                <div class="col-md-4">
                                    <label for="cpf" class="form-label">{{ __('CPF') }} <span class="text-danger">*</span></label>
                                    <input id="cpf" type="text" class="form-control @error('cpf') is-invalid @enderror" name="cpf" value="{{ old('cpf', $user->cpf) }}" required autocomplete="cpf" placeholder="000.000.000-00">
                                    @error('cpf')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Campo Telefone (mantém name="telefone") --}}
                                <div class="col-md-4">
                                    <label for="telefone" class="form-label">{{ __('Telefone') }} <span class="text-danger">*</span></label>
                                    <input id="telefone" type="text" class="form-control @error('telefone') is-invalid @enderror" name="telefone" value="{{ old('telefone', $user->telefone) }}" required autocomplete="telefone" placeholder="(00) 90000-0000">
                                    @error('telefone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Campo Cargo (mantém name="cargo") --}}
                                <div class="col-md-4">
                                    <label for="cargo" class="form-label">{{ __('Cargo') }} <span class="text-danger">*</span></label>
                                    {{-- Usa form-select e preenche com $user->cargo_id --}}
                                    <select class="form-select @error('cargo') is-invalid @enderror" id="cargo" name="cargo" required>
                                        <option value="" disabled>Selecione...</option>
                                        {{-- Assumindo que o valor armazenado é cargo_id e o name enviado é 'cargo' --}}
                                        <option value="1" {{ old('cargo', $user->cargo_id) == 1 ? 'selected' : '' }}>Administrador</option>
                                        <option value="2" {{ old('cargo', $user->cargo_id) == 2 ? 'selected' : '' }}>Usuário Comum</option>
                                        {{-- Se tiver $cargos do controller, adapte o foreach e a comparação --}}
                                        {{-- Ex: @foreach($cargos as $cargoOp)
                                                 <option value="{{ $cargoOp->id }}" {{ old('cargo', $user->cargo_id) == $cargoOp->id ? 'selected' : '' }}>{{ $cargoOp->nome }}</option>
                                             @endforeach --}}
                                    </select>
                                    @error('cargo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div> {{-- fim row g-3 --}}
                        </fieldset>

                        {{-- Seção: Senha (Opcional) --}}
                        {{-- Se você NÃO quiser permitir a alteração de senha nesta tela, mantenha comentado ou remova --}}
                        {{-- Se QUISER permitir, descomente e ajuste --}}
                        {{--
                        <fieldset class="mb-4">
                             <legend class="fs-6 fw-bold border-bottom pb-2 mb-3">Alterar Senha (opcional)</legend>
                             <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">{{ __('Nova Senha') }}</label>
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="new-password">
                                    <small class="text-muted">Deixe em branco para não alterar a senha.</small>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="password-confirm" class="form-label">{{ __('Confirmar Nova Senha') }}</label>
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" autocomplete="new-password">
                                </div>
                            </div>
                        </fieldset>
                        --}}


                        {{-- Seção: Ações (padrão veiculos.edit) --}}
                        <div class="row mt-4 pt-3 border-top">
                            <div class="col-12 d-flex justify-content-end">
                                <a href="{{ route('teste.index') }}" class="btn btn-secondary me-2"> {{-- Link para a listagem --}}
                                   <i class="fa fa-times me-1"></i> Cancelar
                                </a>
                                {{-- Botão de salvar atualizado --}}
                                <button type="submit" class="btn btn-primary"> {{-- Usando primary como em veiculo.edit --}}
                                    <i class="fa fa-save me-1"></i> Salvar Alterações
                                </button>
                            </div>
                        </div>
                    </form> {{-- Fim do Formulário Principal --}}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Estilos Customizados (copiado de veiculos.edit) --}}
<style>
    .custom-card-header {
        background-color: #2c3e50; /* Azul ardósia escuro */
    }
    /* Estilo não necessário se não usar botões pequenos outline */
    /* .btn-outline-primary.btn-sm.py-0 { ... } */
</style>

{{-- Script para fechar alertas (padrão veiculos.edit) --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const allAlerts = document.querySelectorAll('.alert-dismissible');
        allAlerts.forEach(alertEl => {
            let timeout = alertEl.querySelector('ul') ? 7000 : 5000; // Tempo maior se houver lista de erros
            setTimeout(() => {
                // Usar getOrCreateInstance para garantir compatibilidade com Bootstrap 5
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
                 // Verificar se a instância existe antes de chamar close
                if(bsAlert) {
                    bsAlert.close();
                } else if (alertEl && alertEl.parentNode) {
                    // Fallback: remover o elemento se a instância não for encontrada (raro)
                     alertEl.parentNode.removeChild(alertEl);
                }
            }, timeout);
        });
    });
</script>

@endsection 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>