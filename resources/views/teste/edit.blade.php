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
    <h1>Editar Usuário: {{ $user->name }}</h1>
@stop

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12"> {{-- Coluna mais larga como em veiculos --}}
            <div class="card shadow-sm"> {{-- Adicionado shadow-sm --}}
                <div class="card-header custom-card-header text-white"> {{-- Estilo de header de veiculos --}}
                    <h5 class="mb-0"> <i class="fa fa-pencil me-2"></i> Editar Informações do Usuário</h5>
                </div>

                <div class="card-body p-4"> {{-- Adicionado p-4 --}}
                    <form method="POST" action="{{ route('teste.update', $user->id) }}" id="edit-user-form">
                        @csrf
                        @method('PUT') {{-- Essencial para update --}}

                        {{-- Bloco de exibição de erros e sucesso --}}
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="sessionErrorMessage">
                                {{ session('error') }}
                            </div>
                        @endif
                        @if (session('success') || session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert" id="sessionSuccessMessage">
                                {{ session('success') ?? session('success') }}
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="validationErrorMessage">
                                <h6 class="alert-heading">Por favor, corrija os erros abaixo:</h6>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <fieldset class="mb-4">
                            <legend class="fs-6 fw-bold border-bottom pb-2 mb-3">Dados Pessoais e Contato</legend>
                            <div class="row g-3"> {{-- row com gap g-3 --}}

                                {{-- Campo Nome --}}
                                <div class="col-md-6">
                                    <label for="name" class="form-label">{{ __('Nome Completo') }} <span class="text-danger">*</span></label>
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" autofocus>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Campo Email --}}
                                <div class="col-md-6">
                                    <label for="email" class="form-label">{{ __('Endereço de E-mail') }} <span class="text-danger">*</span></label>
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Campo CPF --}}
                                <div class="col-md-4">
                                    <label for="cpf" class="form-label">{{ __('CPF') }} <span class="text-danger">*</span></label>
                                    <input id="cpf" type="text" class="form-control @error('cpf') is-invalid @enderror" name="cpf" value="{{ old('cpf', $user->cpf) }}" required autocomplete="cpf" placeholder="000.000.000-00">
                                    @error('cpf')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Campo Telefone --}}
                                <div class="col-md-4">
                                    <label for="telefone" class="form-label">{{ __('Telefone') }} <span class="text-danger">*</span></label>
                                    <input id="telefone" type="text" class="form-control @error('telefone') is-invalid @enderror" name="telefone" value="{{ old('telefone', $user->telefone) }}" required autocomplete="telefone" placeholder="(00) 90000-0000">
                                    @error('telefone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Campo Cargo --}}
                                <div class="col-md-4">
                                    <label for="cargo" class="form-label">{{ __('Cargo') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('cargo') is-invalid @enderror" id="cargo" name="cargo" required>
                                        <option value="" disabled>Selecione...</option>
                                        <option value="1" {{ old('cargo', $user->cargo_id) == 1 ? 'selected' : '' }}>Administrador</option>
                                        <option value="2" {{ old('cargo', $user->cargo_id) == 2 ? 'selected' : '' }}>Usuário Comum</option>
                                    </select>
                                    @error('cargo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div> {{-- fim row g-3 --}}
                        </fieldset>

                        {{-- Seção de Ações --}}
                        <div class="row mt-4 pt-3 border-top">
                            <div class="col-12 d-flex justify-content-end">
                                <a href="{{ route('teste.index') }}" class="btn btn-secondary me-2">
                                   <i class="fa fa-times me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save me-1"></i> Salvar Alterações
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-card-header {
        background-color: #2c3e50;
    }
</style>
@stop