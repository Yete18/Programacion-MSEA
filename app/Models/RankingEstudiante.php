<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RankingEstudiante extends Model
{
    protected $table = 'ranking_estudiantes';

    protected $primaryKey = 'id_estudiante';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_estudiante',
        'nombres',
        'apellido_paterno',
        'puntaje_total',
        'posicion',
    ];

    protected function casts(): array
    {
        return [
            'puntaje_total' => 'decimal:2',
            'posicion' => 'integer',
        ];
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante', 'id_estudiante');
    }
}
