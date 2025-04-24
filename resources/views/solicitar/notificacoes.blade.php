@extends('layouts.darkMode')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<link rel="stylesheet" href="{{ asset('css/custom-dark-mode.css') }}">
@endsection

@section('content')
<div class="container">
    <h1>Notificações</h1>
    
    @forelse ($notifications as $notification)
        <div class="notification-item p-3 mb-2 @if($notification->read_at) bg-light @else bg-warning @endif" data-id="{{ $notification->id }}">
            <div class="d-flex justify-content-between">
                <div>
                    <strong>{{ $notification->data['user_name'] }}</strong> solicitou o veículo: {{ $notification->data['veiculo'] }}
                </div>
                <div class="text-muted">
                    <small>{{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}</small>
                </div>
            </div>

            <div class="mt-2">
                <p>{{ $notification->data['description'] ?? 'Sem descrição' }}</p>
            </div>

            <!-- Botão "Definir como lida" -->
            @if (!$notification->read_at)
                <button class="btn btn-sm btn-primary mark-as-read" data-id="{{ $notification->id }}">Definir como lida</button>
            @endif
        </div>
    @empty
        <div class="alert alert-info">Você não tem notificações.</div>
    @endforelse
</div>
@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Função para marcar notificações como lidas
        const markAsRead = (notificationId) => {
            fetch(`/notificacoes/${notificationId}/marcar-como-lida`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualiza a interface para refletir a mudança
                    const notificationItem = document.querySelector(`[data-id="${notificationId}"]`);
                    notificationItem.classList.remove('bg-warning'); // Remove a classe de notificação não lida
                    notificationItem.classList.add('bg-light'); // Adiciona a classe de notificação lida
                    const button = notificationItem.querySelector('.mark-as-read');
                    button.style.display = 'none'; // Oculta o botão após marcar como lida
                }
            })
            .catch(error => console.error('Erro ao marcar notificação como lida:', error));
        };

        // Event listener para o botão "Definir como lida"
        document.querySelectorAll('.mark-as-read').forEach(button => {
            button.addEventListener('click', function () {
                const notificationId = this.getAttribute('data-id');
                markAsRead(notificationId);
            });
        });
    });
</script>
@endsection
