@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Lista de Pacientes</h1>
    <div>
        <a href="{{ route('pacientes.create') }}" class="btn btn-primary">Agregar Paciente</a>
        <a href="{{ route('areas.index') }}" class="btn btn-secondary">Gestionar Áreas</a>
        <a href="{{ route('estados.index') }}" class="btn btn-secondary">Gestionar Estados</a>
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
