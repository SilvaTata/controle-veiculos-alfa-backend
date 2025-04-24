<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Veiculo extends Model
{
    use HasFactory;
    protected $fillable = [
        'placa',
        'placa_confirmar',
        'placa_confirmar2',
        'velocimentro_inicio',
        'velocimetro_final',
        'chassi',
        'funcionamento',
        'ano',
        'modelo',
        'marca',
        'cor',
        'capacidade',
        'km_atual',
        'km_revisao',
        'observacao',
        'qr_code',
    ];
    public function solicitacoes()
    {
        return $this->hasMany(Solicitar::class);
    }

}
