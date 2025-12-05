<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Area;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PacienteController extends Controller
{
    /**
     * Muestra la lista de pacientes.
     */

    public function index(Request $request)
    {
        // Obtener el valor de búsqueda si existe
        $search = $request->query('search');

        // Query inicial para obtener los pacientes con sus relaciones
        $pacientes = Paciente::with('area', 'estado')
            ->when($search, function ($query, $search) {
                return $query->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%")
                            ->orWhere('identificacion', 'like', "%{$search}%")
                            ->orWhere('procedimiento', 'like',  "%{$search}%" );
            })
            ->paginate(10); // Usamos paginación para grandes cantidades de pacientes

        // Retornar la vista con los pacientes filtrados
        return view('pacientes.index', compact('pacientes'));
    }


    /**
     * Muestra el formulario para crear un nuevo paciente.
     */
    public function create()
    {
        // Carga las áreas y estados para los selects
        $areas = Area::all();
        $estados = Estado::all();
        return view('pacientes.create', compact('areas', 'estados'));
    }

    private function validatePaciente(Request $request, $paciente = null)
    {
        $uniqueRule = $paciente ? 'unique:pacientes,identificacion,' . $paciente->id : 'unique:pacientes';

        return $request->validate([
            'nombre' => 'required',
            'apellido' => 'nullable',
            'identificacion' => ['required', $uniqueRule],
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'nullable',
            'edad' => 'nullable|integer',
            'email' => 'nullable|email',
            'telefono' => 'nullable|string',
            'direccion' => 'nullable|string',
            'procedimiento' => 'required|string',
            'area_id' => 'nullable|exists:areas,id',
            'estado_id' => 'nullable|exists:estados,id',
            'fecha_cita' => 'required|date',
            'hora_cita' => 'required|date_format:g:i A',  // Formato de 12 horas con AM/PM
            'observacion' => 'nullable|string',
        ]);
    }


    /**
     * Almacena un nuevo paciente en la base de datos.
     */
    public function store(Request $request)
    {
        $validatedData = $this->validatePaciente($request);

        // Convertir hora_cita al formato 24 horas antes de guardar
        $validatedData['hora_cita'] = Carbon::createFromFormat('g:i A', $validatedData['hora_cita'])->format('H:i:s');
    
        // Crear el paciente
        Paciente::create($validatedData);
    
        return redirect()->route('pacientes.index')->with('success', 'Paciente registrado exitosamente');
    }
    

    /**
     * Muestra el formulario para editar un paciente.
     */
    public function edit(Paciente $paciente)
    {
        // Carga las áreas y estados para los selects
        $areas = Area::all();
        $estados = Estado::all();

        return view('pacientes.edit', compact('paciente', 'areas', 'estados'));
    }

    /**
     * Actualiza un paciente en la base de datos.
     */
    public function update(Request $request, Paciente $paciente)
    {
        // Validar los datos
        $validatedData = $this->validatePaciente($request, $paciente);

        // Convertir hora_cita al formato 24 horas antes de guardar
        $validatedData['hora_cita'] = Carbon::createFromFormat('g:i A', $validatedData['hora_cita'])->format('H:i:s');

        // Actualizar el paciente con los datos validados
        $paciente->update($validatedData);

        // Redirigir al listado con un mensaje de éxito
        return redirect()->route('pacientes.index')->with('success', 'Paciente actualizado exitosamente.');
    }

    

    /**
     * Elimina un paciente de la base de datos.
     */
    public function destroy(Paciente $paciente)
    {
        // Eliminar el paciente
        $paciente->delete();

        // Redirigir a la lista de pacientes con un mensaje de éxito
        return redirect()->route('pacientes.index')->with('success', 'Paciente eliminado exitosamente');
    }

public function salaEspera()
{
    // Fecha actual (zona horaria Colombia)
    $fechaHoy = Carbon::now('America/Bogota')->toDateString();

    // Pacientes con cita hoy
    $pacientes = Paciente::with('area', 'estado')
        ->where('estado_id', '!=', 9) // Aquí puedes filtrar por el estado
        ->whereDate('fecha_cita', $fechaHoy)
        ->orderBy('hora_cita', 'asc')
        ->get();

    // Retornar la vista con los pacientes del día
    return view('pacientes.salaEspera', compact('pacientes'));
}
    

}
