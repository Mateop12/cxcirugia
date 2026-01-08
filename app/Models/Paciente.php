<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Area;
use App\Models\Estado;
use App\Models\Destino;



class Paciente extends Model
{
    protected $fillable = [
        'nombre', 'apellido', 'identificacion', 'fecha_nacimiento', 'genero', 'edad', 'email', 
        'telefono', 'direccion', 'procedimiento', 'area_id', 'estado_id','fecha_cita', 'hora_cita', 'observacion',
        'numero_turno', 'turno_llamado_at', 'destino_id'
    ];

    protected $casts = [
        'turno_llamado_at' => 'datetime',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }

    public function destino()
    {
        return $this->belongsTo(Destino::class);
    }

    /**
     * Scope para obtener pacientes con turno llamado (activos en pantalla TV)
     */
    public function scopeTurnosActivos($query)
    {
        return $query->whereNotNull('turno_llamado_at')
                     ->whereNotNull('destino_id')
                     ->whereDate('fecha_cita', today())
                     ->orderByDesc('turno_llamado_at');
    }

    /**
     * Scope para obtener pacientes en espera (con turno pero no llamados)
     */
    public function scopeEnEspera($query)
    {
        return $query->whereNotNull('numero_turno')
                     ->whereNull('turno_llamado_at')
                     ->whereDate('fecha_cita', today())
                     ->orderBy('numero_turno');
    }

    /**
     * Scope para pacientes del día sin turno asignado
     */
    public function scopeSinTurno($query)
    {
        return $query->whereNull('numero_turno')
                     ->whereDate('fecha_cita', today())
                     ->orderBy('hora_cita');
    }
}



