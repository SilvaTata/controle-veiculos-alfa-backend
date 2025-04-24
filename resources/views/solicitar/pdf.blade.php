<!DOCTYPE html>
<html>
<head>
    <title>Relatório de uso do Veículo</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        /* Seus estilos CSS permanecem os mesmos */
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        .header h1 { margin: 0; }
        .details { margin-top: 20px; line-height: 1.6; }
        .details h2 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 10px; font-size: 1.2em; }
        .details p { margin: 5px 0; }
        .details strong { display: inline-block; min-width: 180px; }
        hr { margin-top: 25px; margin-bottom: 25px; border: 0; border-top: 1px solid #ccc; }
        .footer { text-align: center; margin-top: 30px; font-size: 0.9em; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Uso do Veículo</h1>
    </div>

    <div class="details">
        <h2>Solicitação O.S: {{ $solicitar->id }}</h2>
        <p><strong>Colaborador:</strong> {{ $solicitar->user->name }}</p>
        <p><strong>ID do Colaborador:</strong> {{ $solicitar->user->id }}</p>
        <p><strong>Status da Solicitação:</strong> {{ ucfirst($solicitar->situacao) }}</p>

        {{-- Detalhes da Aprovação/Recusa --}}
        @if($solicitar->situacao == 'recusada')
            <p><strong>Recusada por:</strong> {{ $userAdm }}</p>
            <p><strong>Data/Hora Recusa:</strong>
                {{ \Carbon\Carbon::parse($solicitar->data_recusa)->format('d-m-y') }}
                às {{ \Carbon\Carbon::parse($solicitar->hora_recusa) }}
            </p>
             <p><strong>Motivo da Recusa:</strong> {!! nl2br(e($solicitar->motivo_recusa)) !!}</p>
        @elseif (in_array($solicitar->situacao, ['aceita', 'concluída']))
                <p><strong>Aprovada por:</strong> {{ $userAdm }}</p>
                {{-- Acessa os dados de aceite através da relação 'historico' --}}
            @if($solicitar->historico) {{-- Verifica se o histórico existe --}}
            <p><strong>Data/Hora Aceite:</strong>
                 {{-- Assume que data_aceito é um objeto Carbon devido ao casting ou parse necessário --}}
                {{ \Carbon\Carbon::parse($solicitar->historico->data_aceito)->format('d/m/Y') }}
                 às {{ $solicitar->historico->hora_aceito }}
            </p>
            @endif
        @endif
    </div>

    <hr>

    <div class="details">
        <h2>Detalhes do Veículo</h2>
        <p><strong>Veículo Utilizado:</strong>
            {{ $solicitar->veiculo->marca->marca }} {{ $solicitar->veiculo->modelo->modelo }}
        </p>
        <p><strong>Placa:</strong> {{ $solicitar->veiculo->placa }}</p>
    </div>

    @if ($solicitar->situacao == 'concluída' && $solicitar->historico && $solicitar->hist_veiculo)
        <hr>
        <div class="details">
            <h2>Período e Quilometragem de Uso</h2>
            <p><strong>Data/Hora Início (Real):</strong>
                {{ \Carbon\Carbon::parse($solicitar->historico->data_inicio)->format('d/m/Y') }}
                 às {{ $solicitar->historico->hora_inicio }}
            </p>
            <p><strong>Data/Hora Fim (Real):</strong>
                {{ \Carbon\Carbon::parse($solicitar->historico->data_final)->format('d/m/Y') }}
                 às {{ $solicitar->historico->hora_final }}
            </p>
            <p><strong>Quilometragem Inicial:</strong> {{ number_format($solicitar->hist_veiculo->km_inicio, 0, ',', '.') }} km</p>
            <p><strong>Quilometragem Final:</strong> {{ number_format($solicitar->hist_veiculo->km_final, 0, ',', '.') }} km</p>
            <p><strong>Quilômetros Percorridos:</strong> {{ number_format($solicitar->hist_veiculo->km_gasto, 0, ',', '.') }} km</p>
        </div>

        <hr>

        <div class="details">
             <h2>Observações na Devolução</h2>
             {{-- Acessa as observações através da relação 'historico' --}}
             <p>{!! nl2br(e($solicitar->historico->obs_users)) !!}</p>
        </div>
    @endif


    <div class="footer">
        Relatório gerado em: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
    </div>

</body>
</html>