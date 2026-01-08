<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Area;
use App\Models\Estado;
use App\Models\Destino;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdmisionController extends Controller
{
    /**
     * Dashboard de admisión y búsqueda
     */
    public function index()
    {
        return view('admision.index');
    }

    /**
     * Buscar pacientes por cédula o nombre
     */
    public function buscar(Request $request)
    {
        $query = $request->get('query');
        
        if (strlen($query) < 3) {
            return response()->json([]);
        }

        $pacientes = Paciente::where('identificacion', 'like', "%{$query}%")
            ->orWhere('nombre', 'like', "%{$query}%")
            ->orWhere('apellido', 'like', "%{$query}%")
            ->limit(10)
            ->get()
            ->map(function($paciente) {
                // Verificar si ya tiene turno hoy
                $tieneTurnoHoy = $paciente->fecha_cita == Carbon::now('America/Bogota')->toDateString() 
                                 && $paciente->numero_turno != null;
                
                return [
                    'id' => $paciente->id,
                    'nombre' => $paciente->nombre . ' ' . $paciente->apellido,
                    'identificacion' => $paciente->identificacion,
                    'tiene_turno_hoy' => $tieneTurnoHoy,
                    'numero_turno' => $paciente->numero_turno
                ];
            });

        return response()->json($pacientes);
    }

    /**
     * Formulario de ingreso rápido
     */
    public function create()
    {
        $areas = Area::all();
        $destinos = Destino::activos()->get();
        return view('admision.create', compact('areas', 'destinos'));
    }

    /**
     * Guardar paciente y asignar turno
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'identificacion' => 'required|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'area_id' => 'nullable|exists:areas,id',
            'destino_id' => 'nullable|exists:destinos,id',
            'procedimiento' => 'nullable|string',
            'asignar_turno' => 'nullable|boolean'
        ]);

        // Buscar si ya existe o crear nuevo
        $paciente = Paciente::firstOrNew(['identificacion' => $request->identificacion]);
        
        $paciente->fill([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'telefono' => $request->telefono,
            'area_id' => $request->area_id,
            'procedimiento' => $request->procedimiento ?? 'Consulta General',
            'fecha_cita' => Carbon::now('America/Bogota')->toDateString(),
            'hora_cita' => Carbon::now('America/Bogota')->format('H:i:s'),
            'estado_id' => 1, // Estado inicial por defecto (ej: En espera)
        ]);

        // Asignar turno si se solicita
        if ($request->has('asignar_turno')) {
            $fechaHoy = Carbon::now('America/Bogota')->toDateString();
            $siguienteTurno = Paciente::whereDate('fecha_cita', $fechaHoy)
                ->max('numero_turno');
            $paciente->numero_turno = ($siguienteTurno ? $siguienteTurno + 1 : 1);
            
            // Si seleccionó destino inicial, lo asignamos pero NO lo marcamos como llamado
            // para que quede en "En Espera" en el panel
            if ($request->destino_id) {
                // Opcional: Podríamos guardar el destino preferido en algún lado, 
                // pero por ahora el modelo Paciente tiene destino_id que se usa cuando se llama.
                // Si queremos pre-asignar, lo guardamos pero turno_llamado_at queda null.
                // $paciente->destino_id = $request->destino_id; 
            }
        }

        $paciente->save();

        $mensaje = "Paciente registrado correctamente.";
        if ($paciente->numero_turno) {
            $mensaje .= " Turno asignado: #{$paciente->numero_turno}";
        }

        return redirect()->route('admision.index')->with('success', $mensaje);
    }

    /**
     * Asignar turno a paciente existente desde búsqueda
     */
    public function asignarTurno(Paciente $paciente)
    {
        $fechaHoy = Carbon::now('America/Bogota')->toDateString();
        
        // Actualizar fecha de cita a hoy si es diferente
        $paciente->fecha_cita = $fechaHoy;
        $paciente->hora_cita = Carbon::now('America/Bogota')->format('H:i:s');
        
        $siguienteTurno = Paciente::whereDate('fecha_cita', $fechaHoy)
            ->max('numero_turno');
        $paciente->numero_turno = ($siguienteTurno ? $siguienteTurno + 1 : 1);
        $paciente->turno_llamado_at = null; // Resetear llamado
        $paciente->destino_id = null; // Resetear destino
        
        $paciente->save();

        return response()->json([
            'success' => true,
            'message' => "Turno #{$paciente->numero_turno} asignado a {$paciente->nombre}",
            'turno' => $paciente->numero_turno
        ]);
    }
}
