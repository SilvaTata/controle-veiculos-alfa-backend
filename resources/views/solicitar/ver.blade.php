@extends('layouts.darkMode')

@section('content_header')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/custom-dark-mode.css') }}">

    <h1>
        @if (auth()->user()->cargo_id == 1)
            Solicitação de: {{ optional($solicitacao->user)->name }} (ID: {{ $solicitacao->id }})
        @else
            Detalhes da Minha Solicitação (ID: {{ $solicitacao->id }})
        @endif
    </h1>

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
@endsection

@section('content')
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

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-gradient-dark text-white">
             <h5 class="mb-0"> <i class="fa fa-info-circle me-2"></i> Detalhes da Solicitação </h5>
        </div>

        <div class="card-body">
            <fieldset class="mb-4">
                <legend class="fs-6 fw-bold border-bottom pb-2 mb-3"><i class="fa fa-car me-2"></i> Veículo Solicitado</legend>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <strong>Veículo:</strong> {{ optional($solicitacao->veiculo->marca)->marca }} {{ optional($solicitacao->veiculo->modelo)->modelo }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Placa:</strong> {{ optional($solicitacao->veiculo)->placa }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>KM Atual Registrado:</strong> {{ number_format(optional($solicitacao->veiculo)->km_atual ?? 0, 0, ',', '.') }} km
                    </div>
                     <div class="col-md-6 mb-2">
                        <strong>Próxima Revisão (KM):</strong> {{ number_format(optional($solicitacao->veiculo)->km_revisao ?? 0, 0, ',', '.') }} km
                    </div>
                    <div class="col-12 mb-2">
                        <strong>Observações do Veículo:</strong> {!! nl2br(e(optional($solicitacao->veiculo)->obs_veiculo ?? 'Nenhuma')) !!}
                    </div>
                </div>
            </fieldset>

            <fieldset class="mb-4">
                <legend class="fs-6 fw-bold border-bottom pb-2 mb-3"><i class="fa fa-calendar me-2"></i> Período e Motivo (Solicitado)</legend>
                 <div class="row">
                     <div class="col-md-6 mb-2">
                        <strong>Data Retirada Prevista:</strong> {{ \Carbon\Carbon::parse($solicitacao->prev_data_inicio)->format('d/m/Y') }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Hora Retirada Prevista:</strong> {{ \Carbon\Carbon::parse($solicitacao->prev_hora_inicio)->format('H:i') }}
                    </div>
                     <div class="col-md-6 mb-2">
                        <strong>Data Devolução Prevista:</strong> {{ \Carbon\Carbon::parse($solicitacao->prev_data_final)->format('d/m/Y') }}
                    </div>
                     <div class="col-md-6 mb-2">
                        <strong>Hora Devolução Prevista:</strong> {{ \Carbon\Carbon::parse($solicitacao->prev_hora_final)->format('H:i') }}
                    </div>
                     <div class="col-12 mb-2">
                        <strong>Solicitante:</strong> {{ optional($solicitacao->user)->name }}
                    </div>
                    <div class="col-12 mb-2">
                        <strong>Motivo:</strong> {!! nl2br(e($solicitacao->motivo)) !!}
                    </div>
                 </div>
            </fieldset>

            <fieldset class="mb-3">
                 <legend class="fs-6 fw-bold border-bottom pb-2 mb-3"><i class="fa fa-history me-2"></i> Status e Histórico</legend>
                 <div class="row">
                    <div class="col-md-6 mb-2">
                        <strong>Status Atual:</strong>
                        <span class="badge
                            @switch($solicitacao->situacao)
                                @case('pendente') bg-warning text-dark @break
                                @case('aceita') bg-info text-dark @break
                                @case('recusada') bg-danger @break
                                @case('concluída') bg-success @break
                                @default bg-secondary
                            @endswitch
                        ">
                            {{ ucfirst($solicitacao->situacao) }}
                        </span>
                    </div>
                </div>

                    {{-- Detalhes se Recusada
                    @if ($solicitacao->situacao == 'recusada')
                        <div class="col-md-6 mb-2">
                            <strong>Recusada por:</strong> {{ optional($solicitacao->adm)->name ?? 'N/A' }}
                        </div>
                        <div class="col-md-6 mb-2">
                             <strong>Data/Hora Recusa:</strong>
                             {{ $solicitacao->data_recusa ? \Carbon\Carbon::parse($solicitacao->data_recusa)->format('d/m/Y') : '' }}
                             {{ $solicitacao->hora_recusa ? \Carbon\Carbon::parse($solicitacao->hora_recusa)->format('H:i') : '' }}
                        </div>
                        <div class="col-12 mb-2">
                             <strong>Motivo da Recusa:</strong> {!! nl2br(e($solicitacao->motivo_recusa ?? 'Não informado')) !!}
                        </div>
                    @endif

                     @if (in_array($solicitacao->situacao, ['aceita', 'concluída']))
                         <div class="col-md-6 mb-2">
                            <strong>Aprovada por:</strong> {{ optional($solicitacao->adm)->name ?? 'N/A' }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Data/Hora Aceito:</strong>
                             {{ optional($solicitacao->historico)->data_aceito ? \Carbon\Carbon::parse($solicitacao->historico->data_aceito)->format('d/m/Y') : '' }}
                             {{ optional($solicitacao->historico)->hora_aceito ? \Carbon\Carbon::parse($solicitacao->historico->hora_aceito)->format('H:i') : '' }}
                        </div>

                        @if (optional($solicitacao->historico)->data_inicio)
                             <div class="col-md-6 mb-2 mt-2 border-top pt-2">
                                <strong>Início Real:</strong>
                                {{ \Carbon\Carbon::parse($solicitacao->historico->data_inicio)->format('d/m/Y') }}
                                {{ \Carbon\Carbon::parse($solicitacao->historico->hora_inicio)->format('H:i') }}
                            </div>
                             <div class="col-md-6 mb-2 mt-2 border-top pt-2">
                                <strong>KM Inicial:</strong> {{ number_format(optional($solicitacao->hist_veiculo)->km_inicio ?? 0, 0, ',', '.') }} km
                            </div>
                        @endif

                         @if ($solicitacao->situacao == 'concluída')
                            <div class="col-md-6 mb-2">
                                <strong>Fim Real:</strong>
                                {{ optional($solicitacao->historico)->data_final ? \Carbon\Carbon::parse($solicitacao->historico->data_final)->format('d/m/Y') : '' }}
                                {{ optional($solicitacao->historico)->hora_final ? \Carbon\Carbon::parse($solicitacao->historico->hora_final)->format('H:i') : '' }}
                             </div>
                             <div class="col-md-6 mb-2">
                                <strong>KM Final:</strong> {{ number_format(optional($solicitacao->hist_veiculo)->km_final ?? 0, 0, ',', '.') }} km
                            </div>
                             <div class="col-md-6 mb-2">
                                <strong>KM Rodados:</strong> {{ number_format(optional($solicitacao->hist_veiculo)->km_gasto ?? 0, 0, ',', '.') }} km
                            </div>
                            <div class="col-12 mb-2">
                                <strong>Obs. do Usuário na Devolução:</strong> {!! nl2br(e(optional($solicitacao->historico)->obs_users ?? 'Nenhuma')) !!}
                            </div>
                         @endif
                     @endif --}}
                </div> 
            </fieldset>
        </div> 

        <div class="card-footer text-end">
            @if (auth()->user()->cargo_id == 1) 
                @if ($solicitacao->situacao == 'pendente')
                    <form action="{{ route('solicitar.aceitar', $solicitacao->id) }}" method="POST" class="d-inline-block me-1">
                        @csrf
                        @method('POST') 
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-check me-1"></i> Aceitar
                        </button>
                    </form>

                    <a href="{{ route('solicitar.recusar', $solicitacao->id) }}" class="btn btn-danger d-inline-block me-1">
                        <i class="fa fa-times me-1"></i> Recusar
                    </a>
                @endif

            @else 
            @if ($solicitacao->situacao == 'aceita')
            <p class="text-info mt-3"> {{-- Ou text-muted para ainda mais sutil --}}
                <i class="fa fa-mobile me-1"></i>
                <i class="fa fa-play-circle me-1"></i>
                A utilização do veículo deve ser <strong>iniciada</strong> por meio do aplicativo móvel.
            </p>
        @elseif ($solicitacao->situacao == 'em uso') {{-- Seja específico aqui também --}}
            <p class="text-warning mt-3"> {{-- Ou text-info/text-muted --}}
                <i class="fa fa-mobile me-1"></i>
                <i class="fa fa-stop-circle me-1"></i>
                A utilização do veículo deve ser <strong>finalizada</strong> por meio do aplicativo móvel.
            </p>
        @endif
            @endif

            <a class="btn btn-secondary" href="{{ route('solicitar.show', $solicitacao->id) }}"> 
                <i class="fa fa-arrow-left me-1"></i> Voltar
            </a>
        </div>
@endsection