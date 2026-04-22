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
            ->orderBy('id', 'desc')
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
        return $request->validate([
            'nombre' => 'required',
            'apellido' => 'nullable',
            'identificacion' => ['required'],
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'nullable',
            'edad' => 'nullable|integer',
            'email' => 'nullable|email',
            'telefono' => 'nullable|string',
            'direccion' => 'nullable|string',
            'procedimiento' => 'required|string',
            'area_id' => 'nullable|exists:areas,id',
            'estado_id' => 'nullable|exists:estados,id',
            'fecha_cita' => 'nullable|date',
            'hora_cita' => 'nullable|date_format:g:i A',  // Formato de 12 horas con AM/PM
            'observacion' => 'nullable|string',
        ]);
    }


    /**
     * Almacena un nuevo paciente en la base de datos.
     */
    public function store(Request $request)
    {
        $validatedData = $this->validatePaciente($request);

        // Convertir hora_cita al formato 24 horas antes de guardar, si existe
        if (!empty($validatedData['hora_cita'])) {
            $validatedData['hora_cita'] = Carbon::createFromFormat('g:i A', $validatedData['hora_cita'])->format('H:i:s');
        }
    
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

        // Convertir hora_cita al formato 24 horas antes de guardar, si existe
        if (!empty($validatedData['hora_cita'])) {
            $validatedData['hora_cita'] = Carbon::createFromFormat('g:i A', $validatedData['hora_cita'])->format('H:i:s');
        }

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


    /**
     * Descarga una plantilla CSV de ejemplo para carga masiva.
     */
    public function plantilla()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_pacientes.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            // BOM para que Excel abra correctamente con tildes
            fputs($file, "\xEF\xBB\xBF");
            // Encabezados
            fputcsv($file, [
                'nombre',
                'identificacion',
                'genero',
                'procedimiento',
                'area_id',
                'estado_id',
                'fecha_cita',
                'hora_cita',
                'observacion',
            ]);
            // Fila de ejemplo
            fputcsv($file, [
                'Juan Pérez',
                '12345678',
                'Masculino',
                'Colecistectomía',
                '1',
                '1',
                date('Y-m-d'),
                '8:00 AM',
                'Ayuno 8 horas',
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Procesa un archivo CSV para carga masiva de pacientes.
     * Compatible con Excel en español (separador ;) y CSV estándar (separador ,).
     */
    public function importar(Request $request)
    {
        $request->validate([
            'archivo_csv' => 'required|file|mimes:csv,txt|max:2048',
        ], [
            'archivo_csv.required' => 'Debes seleccionar un archivo CSV.',
            'archivo_csv.mimes'    => 'El archivo debe ser de tipo CSV (.csv).',
            'archivo_csv.max'      => 'El archivo no debe superar 2MB.',
        ]);

        $path = $request->file('archivo_csv')->getRealPath();
        $handle = fopen($path, 'r');

        if (!$handle) {
            return back()->with('error_importacion', 'No se pudo leer el archivo.');
        }

        // ── 1. Detectar BOM y delimitador (Excel español usa ; en lugar de ,) ──
        $primeraLinea = fgets($handle);
        $primeraLinea = ltrim($primeraLinea, "\xEF\xBB\xBF"); // quitar BOM
        $delimitador  = (substr_count($primeraLinea, ';') >= substr_count($primeraLinea, ',')) ? ';' : ',';
        rewind($handle);

        // ── 2. Leer encabezados con el delimitador detectado ──
        $encabezados = fgetcsv($handle, 0, $delimitador);
        if ($encabezados) {
            $encabezados[0] = ltrim($encabezados[0], "\xEF\xBB\xBF");
            $encabezados = array_map('trim', $encabezados);
        }

        $requeridos = ['nombre', 'identificacion', 'procedimiento', 'fecha_cita', 'hora_cita'];
        $faltantes  = array_diff($requeridos, $encabezados ?? []);

        if (!empty($faltantes)) {
            fclose($handle);
            return back()->with('error_importacion',
                'El CSV no tiene los encabezados correctos. Faltan: ' . implode(', ', $faltantes) .
                '. Usa la plantilla descargable.');
        }

        $importados  = 0;
        $duplicados  = 0;
        $errores     = [];
        $fila_numero = 1;

        while (($datos = fgetcsv($handle, 0, $delimitador)) !== false) {
            $fila_numero++;

            // Igualar longitud (Excel puede omitir celdas vacías al final)
            $numCols = count($encabezados);
            if (count($datos) < $numCols) {
                $datos = array_pad($datos, $numCols, '');
            } elseif (count($datos) > $numCols) {
                $datos = array_slice($datos, 0, $numCols);
            }

            // Mapear y normalizar (vacíos -> null)
            $fila = array_combine($encabezados, $datos);
            $fila = array_map(function($v) { return trim($v) === '' ? null : trim($v); }, $fila);

            // Validar campos obligatorios
            if (empty($fila['nombre']) || empty($fila['identificacion']) || empty($fila['procedimiento'])) {
                $errores[] = "Fila $fila_numero: campos obligatorios vacíos.";
                continue;
            }

            // ── 3. Parsear fecha_cita ──
            // Acepta: número de serie Excel (46134), DD/MM/YYYY, YYYY-MM-DD
            $fechaCita = null;
            if (!empty($fila['fecha_cita'])) {
                try {
                    $fechaStr = $fila['fecha_cita'];
                    if (is_numeric($fechaStr) && (int)$fechaStr > 1000) {
                        // Número de serie de Excel: días desde 30/12/1899
                        $fechaCita = Carbon::createFromDate(1899, 12, 30)
                            ->addDays((int)$fechaStr)->toDateString();
                    } elseif (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $fechaStr)) {
                        // DD/MM/YYYY (Excel español)
                        $fechaCita = Carbon::createFromFormat('d/m/Y', $fechaStr)->toDateString();
                    } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaStr)) {
                        // YYYY-MM-DD
                        $fechaCita = Carbon::createFromFormat('Y-m-d', $fechaStr)->toDateString();
                    } else {
                        $fechaCita = Carbon::parse($fechaStr)->toDateString();
                    }
                } catch (\Exception $e) {
                    $errores[] = "Fila $fila_numero: fecha_cita inválida ('{$fila['fecha_cita']}'). Use DD/MM/YYYY o YYYY-MM-DD.";
                    continue;
                }
            }

            // Verificar duplicado estricto (misma identificación, mismo procedimiento y misma fecha)
            $duplicadoQuery = Paciente::where('identificacion', $fila['identificacion'])
                                      ->where('procedimiento', $fila['procedimiento']);
            if ($fechaCita) {
                $duplicadoQuery->where('fecha_cita', $fechaCita);
            }
            if ($duplicadoQuery->exists()) {
                $duplicados++;
                continue;
            }

            // ── 4. Parsear hora_cita ──
            // Acepta: fracción decimal Excel (0.333...), '8:00 a. m.', '8:00 AM', '08:00'
            $horaCita = null;
            if (!empty($fila['hora_cita'])) {
                try {
                    $hora = $fila['hora_cita'];

                    if (is_numeric($hora) && (float)$hora >= 0 && (float)$hora < 1) {
                        // Fracción decimal de Excel: 0.333... = 8h de 24h
                        $segundos = round((float)$hora * 86400);
                        $horaCita = gmdate('H:i:s', $segundos);
                    } else {
                        // Normalizar 'a. m.' -> 'AM', 'p. m.' -> 'PM'
                        $hora = preg_replace('/a\.\s*m\./i', 'AM', $hora);
                        $hora = preg_replace('/p\.\s*m\./i', 'PM', $hora);
                        $hora = trim($hora);

                        if (stripos($hora, 'AM') !== false || stripos($hora, 'PM') !== false) {
                            $horaCita = Carbon::createFromFormat('g:i A', strtoupper($hora))->format('H:i:s');
                        } elseif (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $hora)) {
                            // Verifica si la hora tiene segundos (2 vs 3 partes divididas por :)
                            if (substr_count($hora, ':') == 2) {
                                $horaCita = Carbon::createFromFormat('H:i:s', $hora)->format('H:i:s');
                            } else {
                                $horaCita = Carbon::createFromFormat('H:i', $hora)->format('H:i:s');
                            }
                        } else {
                            throw new \Exception("Formato de hora no reconocido: $hora");
                        }
                    }
                } catch (\Exception $e) {
                    $errores[] = "Fila $fila_numero: hora_cita inválida ('{$fila['hora_cita']}'). Use '8:00 AM' o '08:00'.";
                    continue;
                }
            }

            // ── 5. Guardar paciente ──
            try {
                Paciente::create([
                    'nombre'         => $fila['nombre'],
                    'identificacion' => $fila['identificacion'],
                    'genero'         => $fila['genero'] ?? null,
                    'procedimiento'  => $fila['procedimiento'],
                    'area_id'        => !empty($fila['area_id']) ? (int)$fila['area_id'] : null,
                    'estado_id'      => !empty($fila['estado_id']) ? (int)$fila['estado_id'] : null,
                    'fecha_cita'     => $fechaCita,
                    'hora_cita'      => $horaCita,
                    'observacion'    => $fila['observacion'] ?? null,
                ]);
                $importados++;
            } catch (\Exception $e) {
                $errores[] = "Fila $fila_numero: no se pudo guardar — " . $e->getMessage();
            }
        }

        fclose($handle);

        return back()->with([
            'importados'          => $importados,
            'duplicados'          => $duplicados,
            'errores_importacion' => $errores,
        ]);
    }

}
