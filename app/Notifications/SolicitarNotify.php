<?php

namespace App\Notifications;

use App\Models\Solicitar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class SolicitarNotify extends Notification implements ShouldQueue
{
    use Queueable;

    protected $solicitacao;
    protected $tipo;
    protected $mensagem;
    protected $detalhes;

    public function __construct(
        Solicitar $solicitacao,
        string $tipo,
        string $mensagem,
        array $detalhes = []
    ) {
        $this->solicitacao = $solicitacao->load(['user', 'veiculo.marca', 'veiculo.modelo']);
        $this->tipo = $tipo;
        $this->mensagem = $mensagem;
        $this->detalhes = $detalhes;
    }


    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'id' => $this->solicitacao->id,
            'type' => $this->tipo,
            'message' => $this->mensagem,
            'data' => array_merge(
                [
                    'usuario' => $this->solicitacao->user->only('id', 'name'),
                    'veiculo' => $this->solicitacao->veiculo->only('placa', 'cor', 'ano'),
                    'modelo' => $this->solicitacao->veiculo->modelo->modelo,
                    'marca' => $this->solicitacao->veiculo->marca->marca,
                    'data_inicio' => $this->solicitacao->prev_data_inicio,
                    'data_final' => $this->solicitacao->prev_data_final,
                    'hora_inicio' => $this->solicitacao->prev_hora_inicio,
                    'hora_final' => $this->solicitacao->prev_hora_final
                ],
                $this->detalhes
            ),
            'created_at' => now()->toDateTimeString()
        ]);
    }


    public function toArray($notifiable)
    {
        return [
            'tipo' => $this->tipo,
            'mensagem' => $this->mensagem,
            'solicitacao_id' => $this->solicitacao->id,
            'detalhes' => array_merge(
                [
                    'user_id' => $this->solicitacao->user_id,
                    'veiculo_id' => $this->solicitacao->veiculo_id,
                    'data' => $this->solicitacao->prev_data_inicio
                ],
                $this->detalhes
            )
        ];
    }
}
