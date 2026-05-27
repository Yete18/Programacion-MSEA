<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstudianteRecompensa extends Model
{
    protected $table = 'estudiante_recompensa';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id_estudiante',
        'id_recompensa',
        'fecha',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
        ];
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante', 'id_estudiante');
    }

    public function recompensa()
    {
        return $this->belongsTo(Recompensa::class, 'id_recompensa', 'id_recompensa');
    }
}
