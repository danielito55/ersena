<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jornada extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'hora_entrada', 'tolerancia'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}