<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    protected $table = 'tareas';

    protected $primaryKey = 'id_tarea';

    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'descripcion',
        'fecha_creacion',
        'id_profesor',
        'id_elenco',
        'id_estudiante',
        'fecha_limite',
        'xp_recompensa',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_creacion' => 'datetime',
            'fecha_limite' => 'date',
        ];
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'id_profesor', 'id_profesor');
    }

    public function elenco()
    {
        return $this->belongsTo(Elenco::class, 'id_elenco', 'id_elenco');
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante', 'id_estudiante');
    }

    public function ejercicios()
    {
        return $this->belongsToMany(Ejercicio::class, 'tarea_ejercicio', 'id_tarea', 'id_ejercicio')
            ->withPivot('id');
    }

    public function entregas()
    {
        return $this->hasMany(EntregaTarea::class, 'id_tarea', 'id_tarea');
    }
}
