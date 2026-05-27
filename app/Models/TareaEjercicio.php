<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TareaEjercicio extends Model
{
    protected $table = 'tarea_ejercicio';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id_tarea',
        'id_ejercicio',
    ];

    public function tarea()
    {
        return $this->belongsTo(Tarea::class, 'id_tarea', 'id_tarea');
    }

    public function ejercicio()
    {
        return $this->belongsTo(Ejercicio::class, 'id_ejercicio', 'id_ejercicio');
    }
}
