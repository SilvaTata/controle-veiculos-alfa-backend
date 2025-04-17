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
        'solicitar_id',
        'hora_inicio',
        'data_inicio',
        'hora_final',
        'data_final',
        'obs_users'
    ];

    public function solicitar() 
    {
        return $this->belongsTo(solicitar::class, 'solicitar_id');
    }
}
