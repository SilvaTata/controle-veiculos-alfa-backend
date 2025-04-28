@extends('layouts.darkMode') {{-- Mantém o layout base --}}

@section('content_header')
    {{-- Links CSS e Título copiados/adaptados do veiculos.create --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/custom-dark-mode.css') }}">
    <h1>Cadastrar Novo Usuário</h1>
@stop

@section('content')
<div class="container py-4"> {{-- Adicionado py-4 --}}
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12"> {{-- Coluna mais larga como em veiculos --}}
            <div class="card shadow-sm"> {{-- Adicionado shadow-sm --}}
                <div class="card-header custom-card-header text-white"> {{-- Estilo de header de veiculos --}}
                    <h5 class="mb-0"> <i class="fa fa-user-plus me-2"></i> Informações do Usuário</h5> {{-- Título h5 com ícone --}}
                </div>

                <div class="card-body p-4"> {{-- Adicionado p-4 --}}
                    <form method="POST" action="{{ route('teste.store') }}" id="create-user-form"> {{-- ID do form opcional mas bom --}}
                        @csrf

                        {{-- Bloco de exibição de erros e sucesso (padrão veiculos.create) --}}
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

                        {{-- Seção: Dados Pessoais e Contato (usando fieldset como em veiculos) --}}
                        <fieldset class="mb-4">
                            <legend class="fs-6 fw-bold border-bottom pb-2 mb-3">Dados Pessoais e Contato</legend>
                            <div class="row g-3"> {{-- row com gap g-3 --}}

                                {{-- Campo Nome --}}
                                <div class="col-md-6">
                                    <label for="name" class="form-label">{{ __('Nome Completo') }} <span class="text-danger">*</span></label>
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div> {{-- Mudar para div invalid-feedback --}}
                                    @enderror
                                </div>

                                {{-- Campo Email --}}
                                <div class="col-md-6">
                                    <label for="email" class="form-label">{{ __('Endereço de E-mail') }} <span class="text-danger">*</span></label>
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Campo CPF --}}
                                <div class="col-md-4"> {{-- Ajustado para col-md-4 --}}
                                    <label for="cpf" class="form-label">{{ __('CPF') }} <span class="text-danger">*</span></label>
                                    <input id="cpf" type="text" class="form-control @error('cpf') is-invalid @enderror" name="cpf" value="{{ old('cpf') }}" required autocomplete="cpf" placeholder="000.000.000-00">
                                    @error('cpf')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Campo Telefone --}}
                                <div class="col-md-4"> {{-- Ajustado para col-md-4 --}}
                                    <label for="telefone" class="form-label">{{ __('Telefone') }} <span class="text-danger">*</span></label>
                                    <input id="telefone" type="text" class="form-control @error('telefone') is-invalid @enderror" name="telefone" value="{{ old('telefone') }}" required autocomplete="telefone" placeholder="(00) 90000-0000">
                                    @error('telefone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Campo Cargo --}}
                                <div class="col-md-4"> {{-- Ajustado para col-md-4 --}}
                                    <label for="cargo" class="form-label">{{ __('Cargo') }} <span class="text-danger">*</span></label>
                                    {{-- Usando form-select para estilo Bootstrap 5 --}}
                                    <select class="form-select @error('cargo') is-invalid @enderror" id="cargo" name="cargo" required> {{-- Adicionado required --}}
                                        <option value="" disabled {{ old('cargo') ? '' : 'selected' }}>Selecione...</option>
                                        {{-- Mantendo opções hardcoded conforme seu exemplo original --}}
                                        <option value="1" {{ old('cargo') == '1' ? 'selected' : '' }}>Administrador</option>
                                        <option value="2" {{ old('cargo') == '2' ? 'selected' : '' }}>Usuário Comum</option>
                                        {{-- Se você tiver $cargos do controller, use um @foreach aqui --}}
                                    </select>
                                    @error('cargo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div> {{-- fim row g-3 --}}
                        </fieldset>

                        {{-- Seção: Senha de Acesso --}}
                        <fieldset class="mb-4">
                             <legend class="fs-6 fw-bold border-bottom pb-2 mb-3">Senha de Acesso</legend>
                             <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">{{ __('Senha') }} <span class="text-danger">*</span></label>
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="password-confirm" class="form-label">{{ __('Confirmar Senha') }} <span class="text-danger">*</span></label>
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                                    {{-- Erro de confirmação geralmente é ligado ao 'password' --}}
                                </div>
                            </div>
                        </fieldset>

                        {{-- Seção: Ações (padrão veiculos.create) --}}
                        <div class="row mt-4 pt-3 border-top">
                            <div class="col-12 d-flex justify-content-end">
                                <a href="{{ route('teste.index') }}" class="btn btn-secondary me-2"> {{-- Link para a listagem --}}
                                   <i class="fa fa-times me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-success"> {{-- Botão com estilo 'success' --}}
                                    <i class="fa fa-save me-1"></i> Cadastrar Usuário {{-- Texto atualizado --}}
                                </button>
                            </div>
                        </div>
                    </form> {{-- Fim do Formulário Principal --}}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Estilos Customizados (copiado de veiculos.create) --}}
<style>
    .custom-card-header {
        background-color: #2c3e50; /* Azul ardósia escuro */
    }
    /* Manter se precisar de estilos específicos para botões pequenos, senão remover */
    .btn-outline-primary.btn-sm.py-0 {
       line-height: 1.2;
       padding-top: 0.1rem;
       padding-bottom: 0.1rem;
    }
</style>

{{-- Script para fechar alertas (padrão veiculos.create) --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const allAlerts = document.querySelectorAll('.alert-dismissible');
        allAlerts.forEach(alertEl => {
            let timeout = alertEl.querySelector('ul') ? 7000 : 5000;
            setTimeout(() => {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
                if(bsAlert) {
                    bsAlert.close();
                } else if (alertEl && alertEl.parentNode) {
                     alertEl.parentNode.removeChild(alertEl);
                }
            }, timeout);
        });
    });
</script>

@endsection {{-- Fim da section content --}}

{{-- Carregar Bootstrap JS (ESSENCIAL para os alerts dismissible funcionarem) --}}
{{-- Coloque isso no seu layout principal (darkMode.blade.php) antes de </body> se já não estiver lá --}}
{{-- Ou descomente a linha abaixo se não estiver no layout principal --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> --}}