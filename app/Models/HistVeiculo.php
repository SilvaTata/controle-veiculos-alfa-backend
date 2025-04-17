<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class HistVeiculo extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'veiculo_id',
        'solicitacao_id',
        'km_inicio',
        'km_final',
        'km_gasto',
    ];

    public function veiculo() 
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function solicitar()
    {
        return $this->belongsTo(Solicitar::class, 'solicitacao_id');
    }
}
