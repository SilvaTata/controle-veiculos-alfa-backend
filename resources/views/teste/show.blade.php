@extends('layouts.darkMode')
@section('content_header')
<h1>Informações do usuário</h1>

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
            <div class="card-body"><h3>{{ $user->name }}</h3>
                <p><strong>E-mail: </strong> {{ $user->email }}</p>
                <p><strong>CPF: </strong> {{ $user->cpf }}</p>
                <p><strong>Telefone: </strong>{{ $user->telefone }}</p>
                <p><strong>Cargo: </strong> 
                    @if ($user->cargo_id == 1)
                        Adm
                    @endif
                    @if ($user->cargo_id == 2)
                        User
                    @endif
                </p>
                <p><strong>Status:</strong> {{ $user->status }}</p>  
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