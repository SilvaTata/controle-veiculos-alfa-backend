<?php

namespace App\Notifications;

use App\Models\Solicitar;
use App\Models\User;
use App\Models\Veiculo;
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
    protected $veiculo;
    protected $user;

    public function __construct(
        Solicitar $solicitacao,
        Veiculo $veiculo,
        User $user,
        string $tipo,
        string $mensagem,
        array $detalhes = []
    ) {
        $this->solicitacao = $solicitacao->load(['user', 'veiculo.marca', 'veiculo.modelo']);
        $this->tipo = $tipo;
        $this->mensagem = $mensagem;
        $this->detalhes = $detalhes;
        $this->veiculo = $veiculo;
        $this->user = $user;
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
                    'user' => $this->user->only('id', 'name', 'cargo_id'),
                    'veiculo' => $this->veiculo->only('placa', 'cor', 'ano'),
                    'modelo' => $this->veiculo->load('modelo'),
                    'marca' => $this->veiculo->load('marca'),
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
                    'user' => $this->user->only('id', 'name'),
                    'veiculo' => $this->veiculo->only('placa', 'cor', 'ano'),
                    'modelo' => $this->veiculo->only('modelo'),
                    'marca' => $this->veiculo->only('marca'),
                    'data_inicio' => $this->solicitacao->prev_data_inicio,
                    'data_final' => $this->solicitacao->prev_data_final,
                    'hora_inicio' => $this->solicitacao->prev_hora_inicio,
                    'hora_final' => $this->solicitacao->prev_hora_final,
                    'solicitacao' => $this->solicitacao
                ],
                $this->detalhes,
            ),
            'created_at' => now()->toDateTimeString(),
        ];
    }
}