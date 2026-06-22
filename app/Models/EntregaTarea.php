<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntregaTarea extends Model
{
    protected $table = 'entregas_tareas';

    protected $primaryKey = 'id_entrega';

    protected $fillable = [
        'id_tarea',
        'id_estudiante',
        'comentario_estudiante',
        'archivo',
        'estado',
        'calificacion',
        'comentario_profesor',
        'entregado_at',
        'calificado_at',
    ];

    protected function casts(): array
    {
        return [
            'calificacion' => 'decimal:2',
            'entregado_at' => 'datetime',
            'calificado_at' => 'datetime',
        ];
    }
}
