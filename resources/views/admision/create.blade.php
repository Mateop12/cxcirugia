@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-primary text-white p-4 rounded-top-4">
                    <h4 class="mb-0 fw-bold">📝 Registrar Nuevo Paciente</h4>
                    <p class="mb-0 opacity-75">Ingrese los datos básicos para admisión rápida</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admision.store') }}" method="POST">
                        @csrf
                        
                        <div class="row g-3">
                            {{-- Identificación --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Identificación *</label>
                                <input type="text" name="identificacion" class="form-control form-control-lg" required autofocus>
                            </div>

                            {{-- Teléfono --}}
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control form-control-lg">
                            </div>

                            {{-- Nombre --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nombres *</label>
                                <input type="text" name="nombre" class="form-control form-control-lg" required>
                            </div>

                            {{-- Apellido --}}
                            <div class="col-md-6">
                                <label class="form-label">Apellidos</label>
                                <input type="text" name="apellido" class="form-control form-control-lg">
                            </div>

                            

                            <hr class="my-4">

                            {{-- Opciones de Turno --}}
                            <div class="col-12">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="asignar_turno" id="asignarTurno" value="1" checked>
                                    <label class="form-check-label fw-bold" for="asignarTurno">Generar turno automáticamente</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold py-3">
                                Guardar y Generar Turno
                            </button>
                            <a href="{{ route('admision.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
