@extends('layouts.app')

@section('content')
<div class="d-flex vh-100 overflow-hidden">

    <div class="video-container flex-shrink-0" style="width: 40%;">
        <video id="videoPlayer" autoplay muted playsinline class="w-100 h-100 object-cover rounded-end">
            <source id="videoSource" src="{{ asset('videos/video1.mp4') }}" type="video/mp4">
            Tu navegador no soporta videos HTML5.
        </video>
    </div>

    <div class="content-container flex-grow-1 d-flex flex-column justify-content-center align-items-center p-4" style="background-color: #e3f2fd;">
  
        <div class="text-center mb-4">
            <img src="{{ asset('images/logoClinic.png') }}" alt="Clínica Genezen" style="height: 120px;">
            <h1 class="display-5 mt-3 fw-bold text-primary">Sala de Espera</h1>
            <!-- Botón para activar sonido -->
            <button id="btnActivarSonido" class="btn btn-success btn-lg mt-2" onclick="activarSonido()">
                🔔 Activar Alertas de Sonido
            </button>
        </div>

        <!-- Tabla de pacientes -->
        <div class="card shadow-lg border-0 rounded-4 w-100" style="max-width: 1200px;">
            <div class="card-body p-0">
                <table id="tablaPacientes" class="table table-hover table-striped mb-0 text-center align-middle">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>FECHA</th>
                            <th>HORA</th>
                            <th>PACIENTE</th>
                            <th>IDENTIFICACIÓN</th>
                            <th>ÁREA</th>
                            <th>ESTADO</th>
                            <th>PROCEDIMIENTO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pacientes as $paciente)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($paciente->fecha_cita)->format('Y-m-d') }}</td>
                                <td>{{ \Carbon\Carbon::parse($paciente->hora_cita)->format('g:i A') }}</td>
                                <td class="fw-semibold">{{ $paciente->nombre }} </td>
                                <td>****{{ substr($paciente->identificacion, -4) }}</td>
                                <td><span class="badge bg-info text-dark px-3 py-2">{{ $paciente->area?->nombre ?? 'No asignada' }}</span></td>
                                <td><span class="badge bg-info text-dark px-3 py-2">{{ $paciente->estado?->nombre ?? 'Sin estado' }}</span></td>
                                <td>{{ ucfirst($paciente->procedimiento) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No hay pacientes con cita para hoy.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Audio de alerta -->
<audio id="alertSound" preload="auto">
    <source src="{{ asset('sounds/alerta.mp3') }}" type="audio/mpeg">
</audio>

<!-- Auto-actualización de la tabla con detección de cambios -->
<script>
    // Almacenar estados actuales de los pacientes
    let estadosAnteriores = {};
    let primeraActualizacion = true;
    let sonidoActivado = false;
    let pacientesConCambio = [];

    // Activar sonido (requiere interacción del usuario)
    function activarSonido() {
        const audio = document.getElementById('alertSound');
        audio.play().then(() => {
            audio.pause();
            audio.currentTime = 0;
            sonidoActivado = true;
            document.getElementById('btnActivarSonido').style.display = 'none';
            console.log('Sonido activado correctamente');
        }).catch(error => {
            console.log('Error activando sonido:', error);
            alert('No se pudo activar el sonido. Intenta de nuevo.');
        });
    }

    // Inicializar estados al cargar la página
    function inicializarEstados() {
        const filas = document.querySelectorAll('#tablaPacientes tbody tr');
        filas.forEach(fila => {
            const celdas = fila.querySelectorAll('td');
            if (celdas.length >= 6) {
                const identificacion = celdas[3].textContent.trim();
                const estado = celdas[5].textContent.trim();
                estadosAnteriores[identificacion] = estado;
            }
        });
    }

    // Reproducir sonido de alerta
    function reproducirAlerta() {
        if (!sonidoActivado) {
            console.log('Sonido no activado. El usuario debe hacer clic en el botón primero.');
            return;
        }
        const audio = document.getElementById('alertSound');
        if (audio) {
            audio.currentTime = 0;
            audio.play().catch(error => {
                console.log('No se pudo reproducir el sonido:', error);
            });
        }
    }

    // Actualizar tabla y detectar cambios
    function actualizarTabla() {
        fetch('{{ route('pacientes.salaEspera') }}')
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const nuevaTabla = doc.querySelector('tbody');
                
                if (!nuevaTabla) return;

                let hayCambios = false;
                const nuevosEstados = {};
                pacientesConCambio = [];

                // Revisar nuevos estados
                const filas = nuevaTabla.querySelectorAll('tr');
                filas.forEach(fila => {
                    const celdas = fila.querySelectorAll('td');
                    if (celdas.length >= 6) {
                        const identificacion = celdas[3].textContent.trim();
                        const estado = celdas[5].textContent.trim();
                        nuevosEstados[identificacion] = estado;

                        // Comparar con estado anterior
                        if (!primeraActualizacion && estadosAnteriores[identificacion] !== undefined) {
                            if (estadosAnteriores[identificacion] !== estado) {
                                hayCambios = true;
                                pacientesConCambio.push(identificacion);
                                console.log(`Cambio detectado: Paciente ${identificacion} cambió de "${estadosAnteriores[identificacion]}" a "${estado}"`);
                            }
                        }
                    }
                });

                // Actualizar estados anteriores
                estadosAnteriores = nuevosEstados;
                primeraActualizacion = false;

                // Actualizar la tabla en el DOM
                document.querySelector('#tablaPacientes tbody').innerHTML = nuevaTabla.innerHTML;

                // Reproducir sonido y resaltar si hay cambios
                if (hayCambios) {
                    reproducirAlerta();
                    resaltarPacientes();
                }
            })
            .catch(error => console.error('Error actualizando la tabla:', error));
    }

    // Resaltar pacientes con cambio de estado
    function resaltarPacientes() {
        const filas = document.querySelectorAll('#tablaPacientes tbody tr');
        filas.forEach(fila => {
            const celdas = fila.querySelectorAll('td');
            if (celdas.length >= 6) {
                const identificacion = celdas[3].textContent.trim();
                if (pacientesConCambio.includes(identificacion)) {
                    fila.classList.add('fila-cambio');
                    setTimeout(() => {
                        fila.classList.remove('fila-cambio');
                    }, 5000);
                }
            }
        });
    }

    // Inicializar al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        inicializarEstados();
    });

    // Actualizar cada 3 segundos para tiempo casi real
    setInterval(actualizarTabla, 3000);
</script>

<!-- Cambio de videos -->
<script>
    const videos = [
        "{{ asset('videos/video1.mp4') }}",
        "{{ asset('videos/video2.mp4') }}",
        "{{ asset('videos/video3.mp4') }}",
        "{{ asset('videos/video4.mp4') }}",
        "{{ asset('videos/video5.mp4') }}",
        "{{ asset('videos/video6.mp4') }}",
        "{{ asset('videos/video7.mp4') }}"
    ];
    let videoIndex = 0;
    const videoPlayer = document.getElementById('videoPlayer');
    const videoSource = document.getElementById('videoSource');
    videoPlayer.addEventListener('ended', () => {
        videoIndex = (videoIndex + 1) % videos.length;
        videoSource.src = videos[videoIndex];
        videoPlayer.load();
        videoPlayer.play();
    });
</script>

<style>
    body {
        background-color: #e0f7fa;
        margin: 0;
        overflow: hidden;
    }

    .object-cover {
        object-fit: cover;
    }

    .video-container video {
        border-right: 6px solid #fff;
        box-shadow: 4px 0 10px rgba(0, 0, 0, 0.15);
    }

    .table thead th {
        background-color: #0044cc !important;
        color: white;
        font-weight: bold;
        text-transform: uppercase;
        text-align: center;
        font-size: 1.1rem;
    }

    .table tbody td {
        text-align: center;
        font-size: 1.2rem;
        padding: 15px;
        color: #333;
    }

    .table-striped > tbody > tr:nth-of-type(odd) {
        background-color: #f8f9fa;
    }

    .card {
        backdrop-filter: blur(5px);
    }

    .badge {
        font-size: 0.95rem;
    }

    /* Animación para resaltar filas con cambio de estado */
    .fila-cambio {
        animation: resaltarFila 1s ease-in-out infinite;
    }

    @keyframes resaltarFila {
        0%, 100% {
            background-color: #ffeb3b !important;
        }
        50% {
            background-color: #4caf50 !important;
        }
    }

    .fila-cambio td {
        color: #000 !important;
        font-weight: bold;
    }
</style>

@endsection
