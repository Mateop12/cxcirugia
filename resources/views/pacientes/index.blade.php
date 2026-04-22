@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Lista de Pacientes</h1>
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('pacientes.create') }}" class="btn btn-primary"><i class="bi bi-person-plus"></i> Agregar Paciente</a>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCargaMasiva">
            <i class="bi bi-upload"></i> Carga Masiva
        </button>
        <a href="{{ route('areas.index') }}" class="btn btn-secondary">Gestionar Áreas</a>
        <a href="{{ route('estados.index') }}" class="btn btn-secondary">Gestionar Estados</a>
    </div>

    {{-- Modal Carga Masiva --}}
    <div class="modal fade" id="modalCargaMasiva" tabindex="-1" aria-labelledby="lblCargaMasiva" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:14px;">
                <div class="modal-header bg-success text-white" style="border-radius:14px 14px 0 0;">
                    <h5 class="modal-title" id="lblCargaMasiva">⬆ Carga Masiva de Pacientes</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1">1. Descarga la plantilla y rellena los datos de los pacientes.</p>
                    <a href="{{ route('pacientes.plantilla') }}" class="btn btn-outline-success btn-sm mb-3">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Descargar Plantilla CSV
                    </a>
                    <p class="mb-1">2. Sube el archivo CSV completado.</p>
                    <form action="{{ route('pacientes.importar') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <input type="file" name="archivo_csv" id="archivo_csv" class="form-control" accept=".csv" required>
                            @error('archivo_csv')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success">Importar</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer text-muted small" style="font-size:.8rem;">
                    Columnas requeridas: nombre, identificacion, genero, procedimiento, area_id, estado_id, fecha_cita, hora_cita
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de búsqueda -->
    <form method="GET" action="{{ route('pacientes.index') }}" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o identificación..." value="{{ request()->query('search') }}">
            <button type="submit" class="btn btn-primary">Buscar</button>
            <a href="{{ route('pacientes.index') }}" class="btn btn-secondary">Limpiar</a>
        </div>
    </form>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Resultados de carga masiva --}}
    @if(session('importados') !== null)
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>✔ Importación completada:</strong>
            {{ session('importados') }} paciente(s) importado(s)
            @if(session('duplicados'))
                , {{ session('duplicados') }} duplicado(s) omitido(s)
            @endif.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error_importacion'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error_importacion') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('errores_importacion') && count(session('errores_importacion')) > 0)
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>⚠ Errores en algunas filas:</strong>
            <ul class="mb-0 mt-1">
                @foreach(session('errores_importacion') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <table class="table table-hover table-bordered">
        <thead class="bg-primary text-white">
            <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Paciente</th>
                <th>Identificación</th>
                <th>Área</th>
                <th>Estado</th>
                <th>Procedimiento</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pacientes as $paciente)
                <tr>
                    <td>{{ $paciente->fecha_cita }}</td>
                    <td>{{ \Carbon\Carbon::parse($paciente->hora_cita)->format('g:i A') }}</td>
                    <td>{{ $paciente->nombre }} {{ $paciente->apellido }}</td>
                    <td>{{ $paciente->identificacion }}</td>
                    <td>{{ $paciente->area ? $paciente->area->nombre : 'Sin Área' }}</td>
                    <td>{{ $paciente->estado ? $paciente->estado->nombre : 'Sin Estado' }}</td>
                    <td>{{ $paciente->procedimiento }}</td>
                    <td>
                        <a href="{{ route('pacientes.edit', $paciente->id) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('pacientes.destroy', $paciente->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No se encontraron pacientes</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="d-flex justify-content-center mt-4">
        {{ $pacientes->links('vendor.pagination.bootstrap-4') }}
    </div>    
</div>
<style>
    /* Tabla estilizada con colores de clínica */
    .table {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .table thead th {
        background-color: #0044cc; /* Azul oscuro */
        color: white;
        text-transform: uppercase;
        font-weight: bold;
        text-align: center;
        border: none;
    }

    .table tbody td {
        text-align: center;
        color: #333;
        padding: 12px;
        border-top: 1px solid #e0e0e0;
    }

    .table tbody tr:nth-child(odd) {
        background-color: #f8f9fa; /* Azul muy claro */
    }

    .table tbody tr:nth-child(even) {
        background-color: white; /* Fondo blanco */
    }

    /* Estilos especiales para el área y el estado */
    .table tbody td.area,
    .table tbody td.estado {
        font-weight: bold;
        font-size: 1.1em;
        color: #0044cc; /* Azul */
        text-transform: uppercase;
    }

    body {
        background-color: #c5def6; /* Color de fondo suave */
    }

    .card {
        background-color: #ffffff; /* Fondo blanco suave para el contenido */
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Sombra suave */
    }

    .table {
        border-radius: 10px;
        overflow: hidden;
    }

    /* Estilos para los títulos y los botones */
    h1 {
        font-size: 2rem;
        color: #0044cc; /* Azul más oscuro para los títulos */
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
    }

    .btn-warning {
        background-color: #ffcc00;
        border-color: #ffcc00;
        color: black;
    }

    .btn-danger {
        background-color: #ff4444;
        color: white;
    }
    /* Espacio entre el formulario de búsqueda y los botones de acción */
    form.mb-4 {
        margin-top: 15px;  /* Ajusta el valor según lo que necesites */
    }
</style>
@endsection
