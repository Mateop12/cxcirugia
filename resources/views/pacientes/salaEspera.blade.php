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

<!-- Auto-actualización de la tabla -->
<script>
    function actualizarTabla() {
        fetch('{{ route('pacientes.salaEspera') }}')
            .then(response => response.text())
            .then(html => {
                const nuevaTabla = new DOMParser().parseFromString(html, 'text/html').querySelector('tbody').innerHTML;
                document.querySelector('#tablaPacientes tbody').innerHTML = nuevaTabla;
            })
            .catch(error => console.error('Error actualizando la tabla:', error));
    }
    setInterval(actualizarTabla, 15000);
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
</style>

@endsection
