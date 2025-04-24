@extends('layouts.darkMode')

@section('content_header')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<link rel="stylesheet" href="{{ asset('css/custom-dark-mode.css') }}">

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" id="message" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" id="errorMessage" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

    <h1>Veículos</h1>

    @if (auth()->user()->cargo_id == 1)
        <a class="btn btn-novo" href="{{ route('veiculos.create') }}">Novo Veículo</a>
        <br>
        <form action="{{ route('veiculos.index') }}" method="GET" style="display: inline-block; margin-left: 10px;">
            <input class="form-control d-inline-block" style="width: auto;" name="search" placeholder="Buscar veículo" value="{{ request('search') }}">
            <button type="submit" class="btn btn-novo">Buscar</button>
        </form>
    @else
        <form action="{{ route('veiculos.index') }}" method="GET">
            <input class="form-control d-inline-block" style="width: auto;" name="search" placeholder="Buscar veículo" value="{{ request('search') }}">
            <button type="submit" class="btn btn-novo">Buscar</button>
        </form>
    @endif

@endsection

@section('content')
   <div class="content">

   <script>
    setTimeout(() => {
        const successMessage = document.getElementById("message");
        if (successMessage) {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(successMessage);
            bsAlert.close();
        }
        const errorMessage = document.getElementById("errorMessage");
         if (errorMessage) {
            const bsErrorAlert = bootstrap.Alert.getOrCreateInstance(errorMessage);
            bsErrorAlert.close();
        }
    }, 5000);
    </script>

   <table class="table table-bordered table-hover">
       <thead class="table-dark">
           <tr>
                <th>Veículo</th>
                <th>Placa</th>
                <th>Status</th>
                <th>Gerenciamento</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($veiculos as $veiculo)
            <tr>
                <td>{{ $veiculo->marca->marca ?? 'N/D' }} - {{ $veiculo->modelo->modelo ?? 'N/D' }}</td>
                <td>{{ $veiculo->placa }}</td>
                <td>
                    @if (auth()->user()->cargo_id == 1)
                        <span class="badge rounded-pill
                            @switch($veiculo->status_veiculo)
                                @case('disponível') bg-success @break
                                @case('em uso') bg-info text-dark @break
                                @case('reservado') bg-warning text-dark @break
                                @case('manutenção') bg-danger @break
                                @default bg-secondary @break
                            @endswitch">
                            {{ ucfirst($veiculo->status_veiculo) }}
                        </span>
                    @else
                        @if ($veiculo->status_veiculo == 'disponível')
                            <div class="carro1" title="Disponível">
                                <i class="fa fa-car" aria-hidden="true"></i>
                            </div>
                        @else
                            <div class="carro2" title="{{ ucfirst($veiculo->status_veiculo) }}">
                                <i class="fa fa-car" aria-hidden="true"></i>
                            </div>
                        @endif
                    @endif
                </td>
                <td>
                    @if (auth()->user()->cargo_id == 1)
                        <a href="{{ route('veiculos.show', $veiculo->id) }}" class="btn btn-info btn-sm" title="Ver Detalhes"><i class="fa fa-eye"></i></a>
                        <a href="{{ route('veiculos.edit', $veiculo->id) }}" class="btn btn-warning btn-sm" title="Editar"><i class="fa fa-pencil"></i></a>
                        <form action="{{ route('veiculos.destroy', $veiculo->id) }}" method="POST" style="display: inline-block" onsubmit="return confirm('Certeza que deseja excluir?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Excluir"><i class="fa fa-trash"></i></button>
                        </form>
                    @else
                        <a href="{{ route('veiculos.show', $veiculo->id) }}" class="btn btn-info btn-sm" title="Ver Detalhes"><i class="fa fa-eye"></i> Ver</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                 <td colspan="4" class="text-center">Nenhum veículo encontrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>


    <style>
        .carro1 i {
            color: green;
            font-size: 1.2em;
        }
        .carro2 i {
            color: red;
            font-size: 1.2em;
        }
        .form-control.d-inline-block {
             vertical-align: middle;
        }
         .btn.d-inline-block {
             vertical-align: middle;
        }
        .badge.rounded-pill {
    width: 100px;
    text-align: center;
    display: inline-block;
}
    </style>

   </div>
@stop