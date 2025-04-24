<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitar extends Model
{
    use HasFactory;
    protected $fillable = [
        'hora_inicial',
        'hora_final',
        'data_inicial',
        'data_final',
        'veiculo_id',
        'situacao',
        'user_id',
        'placa_confirmar',
        'obs_user',
        'motivo_recusado',
        'id_recusado',
        'hora_recusado',
        'id_aceito',
        'hora_aceito',
        'data_aceito',
    ];

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'id_recusado');
    }

    public function responsavel2()
    {
        return $this->belongsTo(User::class, 'id_aceito');
    }

}
