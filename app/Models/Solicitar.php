<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\HistSolicitar;
use App\Models\HistVeiculo;
use App\Models\User;


class Solicitar extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'veiculo_id',
        'prev_hora_inicio',
        'prev_data_inicio',
        'prev_hora_final',
        'prev_data_final',
        'motivo',
        'situacao',
        'motivo_recusa',
        'adm_id',
        'data_recusa',
        'hora_recusa'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id', 'id');
    }

    public function historico()
    {
        return $this->hasOne(HistSolicitar::class, 'solicitacao_id');
    }

    public function hist_veiculo()
    {
        return $this->hasOne(HistVeiculo::class, 'solicitacao_id');
    }

    public function adm()
    {
        return $this->belongsTo(User::class, 'adm_id');
    }
}
