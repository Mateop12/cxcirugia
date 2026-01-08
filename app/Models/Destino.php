<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destino extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'descripcion', 'activo', 'orden'];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Obtiene solo destinos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true)->orderBy('orden');
    }

    /**
     * Relación: Un destino puede tener muchos pacientes asignados
     */
    public function pacientes()
    {
        return $this->hasMany(Paciente::class);
    }
}
