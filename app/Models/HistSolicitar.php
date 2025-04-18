<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class HistSolicitar extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'solicitacao_id',
        'urgente',
        'hora_inicio',
        'data_inicio',
        'hora_final',
        'data_final',
        'obs_users',
        'hora_aceito',
        'data_aceito',
        'adm_id'
    ];

    public function solicitacao()
    {
        return $this->belongsTo(solicitar::class, 'solicitacao_id', 'id');
    }
}
