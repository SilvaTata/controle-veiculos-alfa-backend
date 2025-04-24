@extends('layouts.darkMode')


@section('content_header')
    <h1>Informações do veículo</h1>
    @if(session('success'))
    <div class="alert alert-success" id="message" role="alert">
        {{ session('success') }}
    </div>
    @endif

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
            <div class="info-container">
                <div class="vehicle-info">
                    <h3>{{ $veiculo->placa }}</h3>
                    <p><strong>Marca:</strong> {{ $veiculo->marca->marca }}</p>
                    <p><strong>Modelo:</strong> {{ $veiculo->modelo->modelo }}</p>
                    <p><strong>Ano:</strong> {{ $veiculo->ano }}</p>
                    <p><strong>Placa:</strong> {{ $veiculo->placa }}</p>
                    <p><strong>Cor:</strong> {{ $veiculo->cor }}</p>
                    <p><strong>Capacidade:</strong> {{ $veiculo->capacidade }}</p>
                    <p><strong>Chassi:</strong> {{ $veiculo->chassi }}</p>
                    <p><strong>Km atual:</strong> {{ $veiculo->km_atual }}</p>
                    <p><strong>Quantos Km faltam para a revisão:</strong> {{ $veiculo->km_revisao - $veiculo->velocimetro_final }}</p>
                    <p><strong>Observações do Veículo: </strong>{{ $veiculo->observacao }}</p>
                    <p><strong>Funcionamento:</strong> 
                        @if ($veiculo->funcionamento == 0)
                            Disponível
                        @elseif ($veiculo->funcionamento == 1)
                            Indisponível
                        @endif
                    </p>
                </div>
                <div class="qr-code-container">
                    <p><strong>QR Code:</strong></p>
                    <img id="qrCodeImage" src="{{ asset('qrcodes/' . $veiculo->qr_code) }}" alt="QR Code do veículo">
                    <br>
                    <button onclick="imprimirQRCode()" class="btn btn-info">Imprimir QR Code</button>
                </div>
            </div>
        </div>
        <div class="card-footer">
            @if (auth()->user()->cargo_id == 1)
                <a href="{{ route('solicitar.create', $veiculo->id) }}" class="btn btn-info">Solicitar Veículo</a>
                <a href="{{ url('veiculos/'.$veiculo->id.'/edit') }}" class="btn btn-warning">Editar</a>
                <form action="{{ url('veiculos/'.$veiculo->id) }}" method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</button>
                </form>
                <a class="btn btn-secundary" onclick="history.back()">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            @else
                <a href="{{ route('solicitar.create', $veiculo->id) }}" class="btn btn-info">Solicitar Veículo</a>
                <a class="btn btn-secundary" onclick="history.back()">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            @endif
        </div>
    </div>
</div>

<script>
    function imprimirQRCode() {
        var qrCodeImage = document.getElementById('qrCodeImage').src;

        // Verifique se a URL está correta
        console.log("QR Code Image URL:", qrCodeImage);

        // Cria uma nova janela para impressão
        var printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Imprimir QR Code</title>');
        
        // Adicionando CSS para centralizar e aumentar o QR Code
        printWindow.document.write('<style>');
        printWindow.document.write('body { text-align: center; padding: 50px; }');
        printWindow.document.write('img { width: 300px; height: auto; margin-top: 50px; }'); // Ajuste o tamanho do QR Code
        printWindow.document.write('</style>');

        printWindow.document.write('</head><body>');

        // Cria a imagem na nova janela
        var imgElement = printWindow.document.createElement('img');
        imgElement.src = qrCodeImage;

        // Quando a imagem carregar, então podemos chamar a impressão
        imgElement.onload = function () {
            printWindow.document.body.appendChild(imgElement);
            printWindow.document.write('</body></html>');
            printWindow.document.close(); // Fecha o documento
            printWindow.print(); // Chama a impressão
        };

        // Se a imagem não carregar corretamente
        imgElement.onerror = function () {
            printWindow.document.write('<p>Não foi possível carregar o QR Code.</p>');
            printWindow.document.close();
            printWindow.print();
        };
    }
</script>

@endsection