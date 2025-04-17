<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencias';

    protected $fillable = [
        'user_id',
        'tipo',
        'fecha_hora',
        'registrado_por'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->fecha_hora) {
                $model->fecha_hora = Carbon::parse($model->fecha_hora)->setTimezone('America/Bogota');
            }
        });
    }

    public function getFechaHoraAttribute($value)
    {
        return Carbon::parse($value)->setTimezone('America/Bogota');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function registrador()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}