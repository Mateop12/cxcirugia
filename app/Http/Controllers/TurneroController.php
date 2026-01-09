<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Destino;
use App\Models\TurneroConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TurneroController extends Controller
{
    /**
     * Vista de pantalla TV (pública, sin autenticación)
     */
    public function pantallaTV()
    {
        $config = TurneroConfig::obtenerTodas();
        $turnosVisibles = (int) ($config['turnos_visibles'] ?? 5);
        
        $turnosActivos = Paciente::with('destino')
            ->turnosActivos()
            ->take($turnosVisibles)
            ->get();

        $destinos = Destino::activos()->get();

        return view('turnero.pantallaTv', compact('turnosActivos', 'config', 'destinos'));
    }

    /**
     * API para obtener turnos activos (para polling AJAX)
     */
    public function obtenerTurnosActivos()
    {
        $config = TurneroConfig::obtenerTodas();
        $turnosVisibles = (int) ($config['turnos_visibles'] ?? 5);
        
        $turnosActivos = Paciente::with('destino')
            ->turnosActivos()
            ->take($turnosVisibles)
            ->get()
            ->map(function ($paciente) {
                return [
                    'id' => $paciente->id,
                    'numero_turno' => $paciente->numero_turno,
                    'nombre' => $paciente->nombre,
                    'destino' => $paciente->destino ? $paciente->destino->nombre : 'Sin destino',
                    'turno_llamado_at' => $paciente->turno_llamado_at->format('H:i'),
                    'llamado_timestamp' => $paciente->turno_llamado_at->toIso8601String(),
                    'es_reciente' => $paciente->turno_llamado_at->diffInSeconds(now()) < ((int) ($config['tiempo_parpadeo'] ?? 5000) / 1000)
                ];
            });

        return response()->json([
            'turnos' => $turnosActivos,
            'config' => $config,
            'timestamp' => now()->format('H:i:s')
        ]);
    }

    /**
     * Panel de control para operadores
     */
    public function panelControl()
    {
        // Pacientes del día
        $fechaHoy = Carbon::now('America/Bogota')->toDateString();
        
        $pacientesSinTurno = Paciente::with('area', 'estado')
            ->sinTurno()
            ->where('estado_id', '!=', 9)
            ->get();

        $pacientesEnEspera = Paciente::with('area', 'estado', 'destino')
            ->enEspera()
            ->get();

        $turnosLlamados = Paciente::with('area', 'estado', 'destino')
            ->turnosActivos()
            ->get();

        $destinos = Destino::activos()->get();
        $config = TurneroConfig::obtenerTodas();

        // Siguiente número de turno
        $siguienteTurno = Paciente::whereDate('fecha_cita', $fechaHoy)
            ->max('numero_turno') + 1 ?? 1;

        return view('turnero.panelControl', compact(
            'pacientesSinTurno',
            'pacientesEnEspera',
            'turnosLlamados',
            'destinos',
            'config',
            'siguienteTurno'
        ));
    }

    /**
     * Generar turno para un paciente
     */
    public function generarTurno(Paciente $paciente)
    {
        $fechaHoy = Carbon::now('America/Bogota')->toDateString();
        
        // Obtener siguiente número de turno
        $siguienteTurno = Paciente::whereDate('fecha_cita', $fechaHoy)
            ->max('numero_turno');
        $siguienteTurno = $siguienteTurno ? $siguienteTurno + 1 : 1;

        $paciente->update([
            'numero_turno' => $siguienteTurno
        ]);

        return response()->json([
            'success' => true,
            'message' => "Turno #{$siguienteTurno} asignado",
            'turno' => $siguienteTurno
        ]);
    }

    /**
     * Llamar turno (asignar destino y marcar como llamado)
     */
    public function llamarTurno(Request $request)
    {
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'destino_id' => 'required|exists:destinos,id'
        ]);

        $paciente = Paciente::findOrFail($request->paciente_id);
        
        $paciente->update([
            'destino_id' => $request->destino_id,
            'turno_llamado_at' => now()
        ]);

        $destino = Destino::find($request->destino_id);

        return response()->json([
            'success' => true,
            'message' => "Turno #{$paciente->numero_turno} llamado a {$destino->nombre}",
            'paciente' => [
                'id' => $paciente->id,
                'numero_turno' => $paciente->numero_turno,
                'nombre' => $paciente->nombre,
                'destino' => $destino->nombre
            ]
        ]);
    }

    /**
     * Rellamar turno (actualizar timestamp para que aparezca como reciente)
     */
    public function rellamarTurno(Request $request)
    {
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id'
        ]);

        $paciente = Paciente::findOrFail($request->paciente_id);
        
        $paciente->update([
            'turno_llamado_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => "Turno #{$paciente->numero_turno} llamado nuevamente"
        ]);
    }

    /**
     * Reiniciar turnos del día
     */
    public function reiniciarTurnos()
    {
        $fechaHoy = Carbon::now('America/Bogota')->toDateString();
        
        Paciente::whereDate('fecha_cita', $fechaHoy)
            ->update([
                'numero_turno' => null,
                'turno_llamado_at' => null,
                'destino_id' => null
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Turnos del día reiniciados correctamente'
        ]);
    }

    /**
     * Eliminar turno (limpiar datos de turno del paciente)
     */
    public function eliminarTurno(Request $request)
    {
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id'
        ]);

        $paciente = Paciente::findOrFail($request->paciente_id);
        
        $paciente->update([
            'numero_turno' => null,
            'turno_llamado_at' => null,
            'destino_id' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => "Turno eliminado de la pantalla"
        ]);
    }

    /**
     * Actualizar configuración del turnero
     */
    public function actualizarConfig(Request $request)
    {
        $request->validate([
            'clave' => 'required|string',
            'valor' => 'required|string'
        ]);

        TurneroConfig::establecer($request->clave, $request->valor);

        return response()->json([
            'success' => true,
            'message' => 'Configuración actualizada'
        ]);
    }
}
