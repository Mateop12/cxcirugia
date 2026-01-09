<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Turnero - Clínica Genezen</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* --- Header (Fixed Height) --- */
        #header {
            height: 15vh; /* Proportional height */
            min-height: 100px;
            background-color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 4vw;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            z-index: 10;
        }

        .header-left {
            display: flex;
            flex-direction: column;
        }

        .header-title {
            font-size: 5vh;
            font-weight: 900;
            color: #0088cc;
            margin: 0;
            line-height: 1;
            text-transform: uppercase;
        }

        .header-subtitle {
            font-size: 2vh;
            color: #aaa;
            margin-top: 5px;
            font-weight: 600;
        }

        .header-right img {
            height: 10vh;
            width: auto;
        }

        /* --- Main Body (Flexible) --- */
        #main-body {
            flex: 1;
            display: flex;
            padding: 2vh 2vw;
            gap: 2vw;
            overflow: hidden;
            height: calc(85vh - 8vh); /* Remaining height approx */
        }

        /* Left Side: Clock + Video */
        #left-side {
            width: 45%;
            display: flex;
            flex-direction: column;
            gap: 2vh;
        }

        .clock-container {
            background: white;
            padding: 1.5vh;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        #clockDisplay {
            font-size: 6vh;
            font-weight: 800;
            color: #333;
            display: block;
            line-height: 1;
        }

        .video-container {
            flex: 1;
            background: #000;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .video-container video {
            width: 100%;
            height: 100%;
            object-fit: contain; /* Crucial: prevents video from breaking layout */
        }

        /* Right Side: Turns List */
        #right-side {
            flex: 1;
            background: white;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .table-header {
            display: flex;
            background-color: #f1f5f9;
            padding: 2vh 0;
            border-bottom: 2px solid #e2e8f0;
        }

        .col-header {
            flex: 1;
            text-align: center;
            font-size: 3vh;
            font-weight: 800;
            color: #1e293b;
        }

        #turnos-list {
            flex: 1;
            overflow-y: auto;
            padding: 0;
        }

        .turno-row {
            display: flex;
            padding: 2.5vh 0;
            border-bottom: 1px solid #f1f5f9;
            align-items: center;
        }

        .turno-row:nth-child(even) {
            background-color: #f8fafc;
        }

        .turno-row.reciente {
            background-color: #e0f2fe;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { background-color: #e0f2fe; }
            50% { background-color: #bae6fd; }
            100% { background-color: #e0f2fe; }
        }

        .turno-number {
            flex: 1;
            text-align: center;
            font-size: 7vh;
            font-weight: 900;
            color: #0284c7;
        }

        .turno-destination {
            flex: 1;
            text-align: center;
            font-size: 4vh;
            font-weight: 700;
            color: #334155;
        }

        .empty-state {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3vh;
            color: #94a3b8;
            font-weight: 500;
        }

        /* --- Footer (Fixed Height) --- */
        #footer {
            height: 8vh;
            min-height: 50px;
            background-color: #0088cc;
            display: flex;
            align-items: center;
            overflow: hidden; /* Hide overflow for marquee */
            white-space: nowrap;
            color: white;
            font-weight: 600;
            position: relative;
        }

        #footer p {
            display: inline-block;
            padding-left: 100%; /* Start off-screen */
            animation: marquee 20s linear infinite;
            font-size: 4vh; /* Larger font */
            margin: 0;
        }

        @keyframes marquee {
            0% { transform: translate(0, 0); }
            100% { transform: translate(-100%, 0); }
        }

        /* --- Utils --- */
        .btn-sonido {
            position: fixed;
            bottom: 10vh;
            right: 2vw;
            background: #fbbf24;
            color: #000;
            border: none;
            padding: 1.5vh 3vh;
            border-radius: 50px;
            font-weight: bold;
            font-size: 2vh;
            cursor: pointer;
            z-index: 100;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            transition: transform 0.2s;
        }

        .btn-sonido:hover {
            transform: scale(1.05);
        }

        .btn-sonido.hidden {
            display: none;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div id="header">
        <div class="header-left">
            <h1 class="header-title">TURNERO</h1>
            <!-- <p class="header-subtitle">Pantalla completa</p> -->
        </div>
        <div class="header-right">
            <img src="{{ asset('images/logocondromed.png') }}" alt="Clínica Genezen">
        </div>
    </div>

    <!-- Main Body -->
    <div id="main-body">
        <!-- Left Side -->
        <div id="left-side">
            <div class="clock-container">
                <span id="clockDisplay">--:--</span>
            </div>
            <div class="video-container">
                <video id="videoPlayer" autoplay muted playsinline>
                    <source id="videoSource" src="{{ asset('videos/Video-institucional.mp4') }}" type="video/mp4">
                </video>
            </div>
        </div>

        <!-- Right Side -->
        <div id="right-side">
            <div class="table-header">
                <div class="col-header">Turno</div>
                <div class="col-header">Pasar a:</div>
            </div>
            <div id="turnos-list">
                <!-- JS will populate this -->
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div id="footer">
        <p>Estar pendiente a la pantalla </p>
    </div>

    <!-- Audio & Controls -->
    <audio id="alertSound" preload="auto">
        <source src="{{ asset('sounds/alerta.mp3') }}" type="audio/mpeg">
    </audio>

    <button id="btnActivarSonido" class="btn-sonido" onclick="activarSonido()">
        🔊 Activar Sonido
    </button>

    <script>
        // --- Configuración ---
        const config = {
            refreshInterval: {{ $config['refresh_interval'] ?? 3000 }},
            sonidoActivo: {{ ($config['sonido_activo'] ?? 'true') === 'true' ? 'true' : 'false' }}
        };

        let sonidoActivado = false;

        // --- Reloj ---
        function updateClock() {
            const now = new Date();
            let hours = now.getHours();
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; 
            document.getElementById('clockDisplay').textContent = `${hours}:${minutes} ${ampm}`;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // --- Sonido ---
        function activarSonido() {
            const audio = document.getElementById('alertSound');
            audio.play().then(() => {
                audio.pause();
                audio.currentTime = 0;
                sonidoActivado = true;
                document.getElementById('btnActivarSonido').classList.add('hidden');
            }).catch(e => console.error("Error audio:", e));
        }

        function reproducirAlerta() {
            if (!sonidoActivado || !config.sonidoActivo) return;
            const audio = document.getElementById('alertSound');
            if (audio) {
                audio.currentTime = 0;
                audio.play().catch(e => console.error("Error play:", e));
            }
        }

        // --- Turnos ---
        let turnosEnPantalla = {}; // Mapa ID -> Timestamp

        function actualizarTurnos() {
            fetch('{{ route("turnero.api.activos") }}')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('turnos-list');
                    const turnos = data.turnos;
                    
                    let playSound = false;
                    const nuevosTurnosEnPantalla = {};

                    turnos.forEach(t => {
                        nuevosTurnosEnPantalla[t.id] = t.llamado_timestamp;
                        
                        if (t.es_reciente) {
                            // Si es nuevo O si el timestamp cambió (rellamado)
                            if (!turnosEnPantalla[t.id] || turnosEnPantalla[t.id] !== t.llamado_timestamp) {
                                playSound = true;
                            }
                        }
                    });

                    if (playSound) {
                        reproducirAlerta();
                    }

                    turnosEnPantalla = nuevosTurnosEnPantalla;

                    // Render
                    if (turnos.length === 0) {
                        container.innerHTML = '<div class="empty-state">Esperando turnos...</div>';
                    } else {
                        container.innerHTML = turnos.map(t => `
                            <div class="turno-row ${t.es_reciente ? 'reciente' : ''}" data-id="${t.id}">
                                <div class="turno-number">${t.numero_turno}</div>
                                <div class="turno-destination">${t.destino}</div>
                            </div>
                        `).join('');
                    }
                })
                .catch(e => console.error("Error fetch:", e));
        }

        // --- Video Playlist ---
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
        
        if (videoPlayer) {
            videoPlayer.addEventListener('ended', () => {
                videoIndex = (videoIndex + 1) % videos.length;
                videoSource.src = videos[videoIndex];
                videoPlayer.load();
                videoPlayer.play();
            });
        }

        // --- Init ---
        document.addEventListener('DOMContentLoaded', () => {
            actualizarTurnos();
            setInterval(actualizarTurnos, config.refreshInterval);
        });
    </script>
</body>
</html>
