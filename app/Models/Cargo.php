<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cargo extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nome'
    ];

    public function users(){
        return $this->hasMany(User::class);
    }
}
