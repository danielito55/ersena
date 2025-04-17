<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramaFormacion extends Model
{
    use HasFactory;

    protected $table = 'programa_formacion';

    protected $fillable = [
        'user_id',
        'nombre_programa',
        'numero_ficha',
        'numero_ambiente'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}