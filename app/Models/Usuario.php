<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    protected $primaryKey = 'id_usuario';

    public $timestamps = false;

    protected $fillable = [
        'correo',
        'contrasena',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'ci',
        'celular',
        'direccion',
        'fecha_nacimiento',
        'foto',
        'id_rol',
    ];

    protected $hidden = [
        'contrasena',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
        ];
    }

    public function getAuthPassword(): string
    {
        return (string) $this->contrasena;
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function estudiante()
    {
        return $this->hasOne(Estudiante::class, 'id_usuario', 'id_usuario');
    }

    public function profesor()
    {
        return $this->hasOne(Profesor::class, 'id_usuario', 'id_usuario');
    }

    public function instrumentos()
    {
        return $this->belongsToMany(Instrumento::class, 'usuario_instrumento', 'id_usuario', 'id_instrumento')
            ->withPivot('id');
    }
}
