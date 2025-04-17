<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Veiculo extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'placa',
        'chassi',
        'status_veiculo',
        'qr_code',
        'ano',
        'cor',
        'capacidade',
        'obs_veiculo',
        'km_revisao',
        'marca_id',
        'modelo_id'
    ];

    public function marca() 
    {
        return $this->belongsTo(Marca::class);
    }

    public function modelo() 
    {
        return $this->belongsTo(Modelo::class);
    }

    public function solicitars()
    {
        return $this->hasMany(Solicitar::class, 'veiculo_id');
    }

    public function solicitar()
    {
        return $this->belongsTo(Solicitar::class, 'solicitacao_id');
    }

    public function historico()
    {
        return $this->hasOne(HistVeiculo::class, 'veiculo_id');
    }

    public function histSolicitar()
    {
        return $this->hasOne(HistSolicitar::class, 'solicitacao_id');
    }
}
