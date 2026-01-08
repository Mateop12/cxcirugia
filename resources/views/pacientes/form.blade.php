@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <div class="card-body">
            <h1 class="text-center mb-4 text-primary">Agregar Paciente</h1>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form action="{{ isset($paciente) ? route('pacientes.update', $paciente->id) : route('pacientes.store') }}" method="POST">
                @csrf
                @if(isset($paciente))
                    @method('PUT')
                @endif

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre Completo</label>
                        <input type="text" name="nombre" class="form-control" value="{{ isset($paciente) ? $paciente->nombre : old('nombre') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="identificacion" class="form-label">Identificación</label>
                        <input type="text" name="identificacion" class="form-control" value="{{ isset($paciente) ? $paciente->identificacion : old('identificacion') }}" required>
                    </div>
                    <!-- <div class="col-md-6">
                        <label for="apellido" class="form-label">Apellido</label>
                        <input type="text" name="apellido" class="form-control" value="{{ isset($paciente) ? $paciente->apellido : old('apellido') }}" required>
                    </div> -->
                </div>

                <div class="row mb-3">
                    
                    <!-- <div class="col-md-6">
                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" class="form-control" value="{{ isset($paciente) ? $paciente->fecha_nacimiento : old('fecha_nacimiento') }}" required>
                    </div> -->
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="genero" class="form-label">Género</label>
                        <select name="genero" class="form-select">
                            <option value="" disabled {{ !isset($paciente) ? 'selected' : '' }}>Selecciona un género</option>
                            <option value="Masculino" {{ (isset($paciente) && $paciente->genero == 'Masculino') ? 'selected' : '' }}>Masculino</option>
                            <option value="Femenino" {{ (isset($paciente) && $paciente->genero == 'Femenino') ? 'selected' : '' }}>Femenino</option>
                            <option value="Otro" {{ (isset($paciente) && $paciente->genero == 'Otro') ? 'selected' : '' }}>Otro</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="edad" class="form-label">Edad</label>
                        <input type="number" name="edad" class="form-control" value="{{ isset($paciente) ? $paciente->edad : old('edad') }}" >
                    </div>
                </div>

                <div class="row mb-3">
                    <!-- <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ isset($paciente) ? $paciente->email : old('email') }}">
                    </div> -->
                    <div class="col-md-6">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" value="{{ isset($paciente) ? $paciente->telefono : old('telefono') }}">
                    </div>
                    <div class="col-md-6">
                        <label for="edad" class="form-label">Procedimiento</label>
                        <input type="procedimiento" name="procedimiento" class="form-control" value="{{ isset($paciente) ? $paciente->procedimiento : old('procedimiento') }}" required>
                    </div>
                </div>
                <div class="row mb-3">
                <div class="col-md-6">
                    <!-- <div class="col-md-12">
                        <label for="direccion" class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control" value="{{ isset($paciente) ? $paciente->direccion : old('direccion') }}">
                    </div> -->
                </div>
                 
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="area_id" class="form-label">Área de Servicio</label>
                        <select name="area_id" class="form-select">
                            <option value="">Selecciona un área de servicio</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}" {{ (isset($paciente) && $paciente->area_id == $area->id) ? 'selected' : '' }}>
                                    {{ $area->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="estado_id" class="form-label">Estado</label>
                        <select name="estado_id" class="form-select">
                            <option value="">Selecciona un estado</option>
                            @foreach($estados as $estado)
                                <option value="{{ $estado->id }}" {{ (isset($paciente) && $paciente->estado_id == $estado->id) ? 'selected' : '' }}>
                                    {{ $estado->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="fecha_cita" class="form-label">Fecha de la Cirugía</label>
                        <input type="date" name="fecha_cita" class="form-control" value="{{ isset($paciente) ? $paciente->fecha_cita : old('fecha_cita') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="hora_cita" class="form-label">Hora de la Cirugía</label>
                        <input type="text" name="hora_cita" class="form-control" id="horaCita" value="{{ isset($paciente) ? $paciente->hora_cita : old('hora_cita') }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="observacion" class="form-label">Observación</label>
                        <input type="text" name="observacion" class="form-control" value="{{ isset($paciente) ? $paciente->observacion : old('observacion') }}">
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">{{ isset($paciente) ? 'Actualizar Paciente' : 'Agregar Paciente' }}</button>
                    <a href="{{ route('turnero.panel') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Estilos adicionales -->
<style>
    .form-control, .form-select {
        border-radius: 10px;
        border: 1px solid #ced4da;
        padding: 10px;
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
    }

    .card {
        border-radius: 15px;
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
        padding: 10px 30px;
    }

    .btn-secondary {
        padding: 10px 30px;
    }

    h1 {
        font-size: 2.5rem;
    }
</style>
@endsection
