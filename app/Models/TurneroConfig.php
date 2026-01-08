<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TurneroConfig extends Model
{
    use HasFactory;

    protected $table = 'turnero_config';

    protected $fillable = ['clave', 'valor', 'descripcion'];

    /**
     * Obtener valor de configuración por clave
     */
    public static function obtener(string $clave, $default = null)
    {
        $config = self::where('clave', $clave)->first();
        return $config ? $config->valor : $default;
    }

    /**
     * Establecer valor de configuración
     */
    public static function establecer(string $clave, string $valor)
    {
        return self::updateOrCreate(
            ['clave' => $clave],
            ['valor' => $valor]
        );
    }

    /**
     * Obtener todas las configuraciones como array
     */
    public static function obtenerTodas()
    {
        return self::pluck('valor', 'clave')->toArray();
    }
}
