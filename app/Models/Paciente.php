<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Area;
use App\Models\Estado;



class Paciente extends Model
{
    protected $fillable = [
        'nombre', 'apellido', 'identificacion', 'fecha_nacimiento', 'genero', 'edad', 'email', 
        'telefono', 'direccion', 'procedimiento', 'area_id', 'estado_id','fecha_cita', 'hora_cita', 'observacion'
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }
}

