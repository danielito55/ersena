<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombres_completos',
        'documento_identidad',
        'correo',
        'password',
        'rol',
        'qr_code',
        'profile_photo',
        'jornada_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Verifica si el usuario tiene un rol específico
     *
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->rol === $role;
    }

    public function jornada()
    {
        return $this->belongsTo(Jornada::class);
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function accessLogs()
    {
        return $this->hasMany(AccessLog::class);
    }

    public function programaFormacion()
    {
        return $this->hasOne(ProgramaFormacion::class);
    }
}
