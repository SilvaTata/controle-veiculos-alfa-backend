@extends('layouts.darkMode')
@section('content_header')
<h1>Permissões do {{ $user->name }}</h1>

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
            <h4>Cargo:  @if ($user->cargo_id == 1)
                                            Adm
                                        @endif
                                        @if ($user->cargo_id == 2)
                                            User
                                        @endif
                                    </h4>
            <p><strong>Permissões:</strong></p>
                <ul>
                    @if ($user->cargo_id == 1)
                        <li> Ver, Criar, Editar e Excluir Usuários; </li>
                        <li>Ver, Criar, Editar e Excluir Veículos;</li>
                        <li>Aceitar e Recusar Solicitações;</li>
                    @else 
                    <li>Ver usuários;</li>
                    <li>Ver veículos;</li>
                    <li>Solicitar um veículo.</li>
                    @endif 
                </ul>
        </div>
        <div class="card-footer">
            @if (auth()->user()->cargo_id == 1)
                <a href="{{ url('teste/'.$user->id.'/edit') }}" class="btn btn-warning">Editar</a>
                <form action="{{ url('teste/'.$user->id) }}" method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</button>
                </form>
                <a class="btn btn-secundary" onclick="history.back()">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            @else
                <a class="btn btn-secundary" onclick="history.back()">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            @endif
        </div>
    </div>
</div>
@endsection