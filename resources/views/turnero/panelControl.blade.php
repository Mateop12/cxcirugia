@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold text-primary mb-1">🎫 Panel de Control - Turnero</h1>
            <p class="text-muted mb-0">Gestión de turnos del día: {{ \Carbon\Carbon::now('America/Bogota')->format('d/m/Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('turnero.tv') }}" target="_blank" class="btn btn-outline-primary">
                📺 Ver Pantalla TV
            </a>
            <a href="{{ route('admision.index') }}" class="btn btn-outline-success">
                📋 Admisiones
            </a>
            <a href="{{ route('destinos.index') }}" class="btn btn-outline-secondary">
                ⚙️ Gestionar Destinos
            </a>
            <button class="btn btn-danger" onclick="reiniciarTurnos()">
                🔄 Reiniciar Turnos
            </button>
        </div>
    </div>

    <div class="row g-4">
        {{-- Columna: Pacientes sin turno --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">📋 Sin Turno Asignado</h5>
                    <small>Pacientes del día sin turno</small>
                </div>
                <div class="card-body overflow-auto" style="max-height: 500px;">
                    @forelse($pacientesSinTurno as $paciente)
                        <div class="d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded">
                            <div>
                                <strong>{{ $paciente->nombre }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($paciente->hora_cita)->format('g:i A') }} |
                                    {{ $paciente->area?->nombre ?? 'Sin área' }}
                                </small>
                            </div>
                            <button class="btn btn-sm btn-primary" onclick="generarTurno({{ $paciente->id }})">
                                + Turno
                            </button>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4">No hay pacientes sin turno</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Columna: Pacientes en espera --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">⏳ En Espera</h5>
                    <small>Pacientes con turno, esperando ser llamados</small>
                </div>
                <div class="card-body overflow-auto" style="max-height: 500px;">
                    @forelse($pacientesEnEspera as $paciente)
                        <div class="p-3 mb-2 bg-light rounded border-start border-warning border-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge bg-primary fs-5 px-3 py-2">#{{ $paciente->numero_turno }}</span>
                                </div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($paciente->hora_cita)->format('g:i A') }}</small>
                            </div>
                            <strong>{{ $paciente->nombre }}</strong>
                            <br>
                            <small class="text-muted">{{ $paciente->area?->nombre ?? 'Sin área' }}</small>
                            
                            <div class="mt-2">
                                <div class="input-group input-group-sm">
                                    <select class="form-select form-select-sm" id="destino-{{ $paciente->id }}">
                                        <option value="">Seleccionar destino...</option>
                                        @foreach($destinos as $destino)
                                            <option value="{{ $destino->id }}">{{ $destino->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-success" onclick="llamarTurno({{ $paciente->id }})">
                                        📢 Llamar
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4">No hay pacientes en espera</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Columna: Turnos llamados --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">✅ Turnos Llamados</h5>
                    <small>Activos en pantalla TV</small>
                </div>
                <div class="card-body overflow-auto" style="max-height: 500px;">
                    @forelse($turnosLlamados as $paciente)
                        <div class="p-3 mb-2 bg-light rounded border-start border-success border-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-success fs-5 px-3 py-2">#{{ $paciente->numero_turno }}</span>
                                <button class="btn btn-sm btn-outline-primary" onclick="rellamarTurno({{ $paciente->id }})">
                                    🔔 Rellamar
                                </button>
                            </div>
                            <strong>{{ $paciente->nombre }}</strong>
                            <br>
                            <span class="text-success fw-bold">→ {{ $paciente->destino?->nombre ?? 'Sin destino' }}</span>
                            <br>
                            <small class="text-muted">
                                Llamado: {{ $paciente->turno_llamado_at ? $paciente->turno_llamado_at->format('H:i') : '--:--' }}
                            </small>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4">No hay turnos llamados</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Estadísticas rápidas --}}
    <div class="row g-4 mt-4">
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $pacientesSinTurno->count() }}</h3>
                    <small>Sin turno</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $pacientesEnEspera->count() }}</h3>
                    <small>En espera</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $turnosLlamados->count() }}</h3>
                    <small>Llamados</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $siguienteTurno }}</h3>
                    <small>Siguiente turno</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de alerta --}}
<div class="modal fade" id="alertModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div id="alertIcon" class="mb-2" style="font-size: 3rem;"></div>
                <p id="alertMessage" class="mb-0 fw-bold"></p>
            </div>
        </div>
    </div>
</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function showAlert(message, type = 'success') {
        const modal = document.getElementById('alertModal');
        const icon = document.getElementById('alertIcon');
        const msg = document.getElementById('alertMessage');
        
        icon.textContent = type === 'success' ? '✅' : '❌';
        msg.textContent = message;
        
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        setTimeout(() => bsModal.hide(), 2000);
    }

    async function generarTurno(pacienteId) {
        try {
            const response = await fetch(`{{ url('turnero/generar') }}/${pacienteId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            const data = await response.json();
            if (data.success) {
                showAlert(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message, 'error');
            }
        } catch (error) {
            showAlert('Error al generar turno', 'error');
        }
    }

    async function llamarTurno(pacienteId) {
        const destinoSelect = document.getElementById(`destino-${pacienteId}`);
        const destinoId = destinoSelect.value;
        
        if (!destinoId) {
            showAlert('Selecciona un destino primero', 'error');
            return;
        }

        try {
            const response = await fetch('{{ route('turnero.llamar') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    paciente_id: pacienteId,
                    destino_id: destinoId
                })
            });
            const data = await response.json();
            if (data.success) {
                showAlert(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message, 'error');
            }
        } catch (error) {
            showAlert('Error al llamar turno', 'error');
        }
    }

    async function rellamarTurno(pacienteId) {
        try {
            const response = await fetch('{{ route('turnero.rellamar') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    paciente_id: pacienteId
                })
            });
            const data = await response.json();
            if (data.success) {
                showAlert(data.message);
            } else {
                showAlert(data.message, 'error');
            }
        } catch (error) {
            showAlert('Error al rellamar turno', 'error');
        }
    }

    async function reiniciarTurnos() {
        if (!confirm('¿Estás seguro de reiniciar todos los turnos del día?')) return;
        
        try {
            const response = await fetch('{{ route('turnero.reiniciar') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            const data = await response.json();
            if (data.success) {
                showAlert(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message, 'error');
            }
        } catch (error) {
            showAlert('Error al reiniciar turnos', 'error');
        }
    }

    // Auto-refresh cada 10 segundos
    setInterval(() => {
        location.reload();
    }, 10000);
</script>

<style>
    body {
        background-color: #f0f4f8;
    }
    
    .card-header {
        border-bottom: none;
    }
    
    .card {
        border-radius: 12px;
        overflow: hidden;
    }
    
    .btn {
        border-radius: 8px;
    }
    
    .badge {
        border-radius: 6px;
    }
</style>
@endsection
