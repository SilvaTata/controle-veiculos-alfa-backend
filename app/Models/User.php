<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'cpf',
        'name',
        'email',
        'password',
        'status',
        'telefone',
        'cargo_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ]; 

    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }

    public function solicitars()
    {
        return $this->hasMany(Solicitar::class);
    }

    public function isAdmn()
    {
        return $this->cargo->nome === 'Adm';
    }
}
