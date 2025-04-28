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

@if(session('success') || session('sucess'))
    <div class="alert alert-success alert-dismissible fade show" id="message" role="alert">
        {{ session('success') ?? session('sucess') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" id="errorMessage" role="alert">
        {{ session('error') }}
    </div>
@endif

<h1>Usuários</h1>

@if (auth()->user()->cargo_id == 1)
    <a class="btn btn-novo" href="{{ route('teste.create') }}">Novo Usuário</a>
    <form action="{{ route('teste.index') }}" method="GET" style="display: inline-block; margin-left: 10px;">
        <input class="form-control d-inline-block" style="width: auto;" name="search" placeholder="Buscar usuário" value="{{ request('search') }}">
        <button type="submit" class="btn btn-novo">Buscar</button>
    </form>
@else
    <form action="{{ route('teste.index') }}" method="GET">
        <input class="form-control d-inline-block" style="width: auto;" name="search" placeholder="Buscar usuário" value="{{ request('search') }}">
        <button type="submit" class="btn btn-novo">Buscar</button>
    </form>
@endif
@stop

@section('content')
<div class="content">

{{-- Script para fechar alertas iniciais após DOM pronto --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            const successMessage = document.getElementById("message");
            if (successMessage) {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(successMessage);
                if (bsAlert) {
                    bsAlert.close();
                }
            }
            const errorMessage = document.getElementById("errorMessage");
            if (errorMessage) {
                const bsErrorAlert = bootstrap.Alert.getOrCreateInstance(errorMessage);
                if (bsErrorAlert) {
                    bsErrorAlert.close();
                }
            }
        }, 5000);
    });
</script>

<style>
    .status-indicator {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: inline-block;
        cursor: pointer;
        transition: background-color 0.3s ease;
        vertical-align: middle;
    }
    .status-indicator.active {
        background-color: #00d68f;
    }
    .status-indicator.inactive {
        background-color: crimson;
    }
    .form-control.d-inline-block {
        vertical-align: middle;
    }
    .bolinha1 i, .bolinha2 i {
        font-size: 1.2em;
        vertical-align: middle;
    }
    .bolinha1 i { color: green; }
    .bolinha2 i { color: red; }
    </style>
    
    <script>
    function toggleStatus(userId) {
    const indicator = document.getElementById(`status_${userId}`);
    const isActive = indicator.classList.contains('active');
    const newStatus = isActive ? 'inativo' : 'ativo';

    fetch(`/teste/${userId}/mudarStatusU`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            status: newStatus
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errData => {
                throw new Error(errData.message || `Erro HTTP ${response.status}`);
            }).catch(() => {
                throw new Error(`Erro HTTP ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            indicator.classList.toggle('active', newStatus === 'ativo');
            indicator.classList.toggle('inactive', newStatus === 'inativo');
            indicator.title = `Clique para alterar para ${newStatus === 'ativo' ? 'inativo' : 'ativo'}`;

            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
            alertDiv.style.top = '80px';
            alertDiv.style.right = '20px';
            alertDiv.style.zIndex = '1055';
            alertDiv.setAttribute('role', 'alert');
            alertDiv.innerHTML = `${data.message}`;
            document.body.appendChild(alertDiv);

            setTimeout(() => {
                if (alertDiv) {
                    alertDiv.classList.remove('show');
                    setTimeout(() => alertDiv.remove(), 500);
                }
            }, 5000);

        } else {
            throw new Error(data.message || "Falha ao atualizar status.");
        }
    })
    .catch(error => {
        console.error('Erro ao mudar status do usuário:', error);

        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed';
        alertDiv.style.top = '80px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '1055';
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `Erro ao atualizar status: ${error.message}`;
        document.body.appendChild(alertDiv);

        setTimeout(() => {
            if (alertDiv) {
                alertDiv.classList.remove('show');
                setTimeout(() => alertDiv.remove(), 500);
            }
        }, 5000);
    });
}

    </script>

<table class="table table-bordered table-hover">
    <thead class="table-dark">
        <tr>
            <th>Status</th>
            <th>Nome</th>
            <th>E-mail</th>
            @if (auth()->user()->cargo_id == 1)
                <th>CPF</th>
            @endif
            <th>Permissões</th>
            <th>Gerenciamento</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
            <tr>
                <td>
                    @if (auth()->user()->cargo_id == 1)
                        <div
                            class="status-indicator {{ $user->status === 'ativo' ? 'active' : 'inactive' }}"
                            id="status_{{ $user->id }}"
                            onclick="toggleStatus({{ $user->id }})"
                            title="Clique para alterar para {{ $user->status === 'ativo' ? 'inativo' : 'ativo' }}"
                        ></div>
                    @else
                        @if ($user->status == "ativo")
                            <div class="bolinha1" title="Ativo">
                                <i class="fa fa-user" aria-hidden="true"></i>
                            </div>
                        @else
                            <div class="bolinha2" title="Inativo">
                                <i class="fa fa-user" aria-hidden="true"></i>
                            </div>
                        @endif
                    @endif
                </td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                @if (auth()->user()->cargo_id == 1)
                    <td>{{ $user->cpf }}</td>
                @endif
                <td>
                    <a href="{{ route('teste.permissao', $user->id) }}" title="Gerenciar Permissões">
                        <i class="fa fa-key" aria-hidden="true"></i>
                    </a>
                </td>
                <td>
                    @if (auth()->user()->cargo_id == 1)
                        <a href="{{ route('teste.show', $user->id) }}" class="btn btn-info btn-sm" title="Ver Detalhes">
                            <i class="fa fa-eye" aria-hidden="true"></i>
                        </a>
                        <a href="{{ route('teste.edit', $user->id) }}" class="btn btn-warning btn-sm" title="Editar Usuário">
                            <i class="fa fa-pencil" aria-hidden="true"></i>
                        </a>
                    @else
                        <a href="{{ route('teste.show', $user->id) }}" class="btn btn-info btn-sm" title="Ver Detalhes">
                            <i class="fa fa-eye" aria-hidden="true"></i> Ver
                        </a>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>


@endsection