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
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            color: white;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }

        .header img {
            height: 80px;
        }

        .header-title {
            font-size: 2.5rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 4px;
            background: linear-gradient(90deg, #00d4ff, #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .current-time {
            font-size: 2rem;
            font-weight: 600;
            color: #00d4ff;
        }

        /* Botón activar sonido */
        .btn-sonido {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 30px;
            font-size: 1.2rem;
            font-weight: 600;
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            border-radius: 50px;
            color: white;
            cursor: pointer;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
            transition: all 0.3s ease;
        }

        .btn-sonido:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 30px rgba(16, 185, 129, 0.6);
        }

        .btn-sonido.hidden {
            display: none;
        }

        /* Main content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px;
        }

        /* Turnos grid */
        .turnos-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .turno-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px 50px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            transition: all 0.4s ease;
        }

        .turno-card:first-child {
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.4), rgba(0, 212, 255, 0.3));
            border-color: #7c3aed;
            transform: scale(1.02);
        }

        .turno-card.reciente {
            animation: pulsarTurno 1s ease-in-out infinite;
            border-color: #10b981;
            box-shadow: 0 0 40px rgba(16, 185, 129, 0.5);
        }

        @keyframes pulsarTurno {
            0%, 100% {
                background: linear-gradient(135deg, rgba(16, 185, 129, 0.5), rgba(5, 150, 105, 0.3));
                transform: scale(1.02);
            }
            50% {
                background: linear-gradient(135deg, rgba(16, 185, 129, 0.8), rgba(5, 150, 105, 0.6));
                transform: scale(1.05);
            }
        }

        .turno-numero {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .turno-label {
            font-size: 1.5rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .turno-number {
            font-size: 5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #00d4ff, #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
        }

        .turno-flecha {
            font-size: 3rem;
            color: rgba(255, 255, 255, 0.5);
            margin: 0 20px;
        }

        .turno-destino {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            text-align: right;
        }

        .destino-label {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }

        .destino-nombre {
            font-size: 2.5rem;
            font-weight: 700;
            color: #10b981;
            text-transform: uppercase;
        }

        .turno-hora {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.4);
            margin-top: 10px;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 80px 40px;
        }

        .empty-state .icon {
            font-size: 6rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h2 {
            font-size: 2.5rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.4);
        }

        /* Footer */
        .footer {
            padding: 20px 40px;
            background: rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .footer p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 1rem;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .turno-number {
                font-size: 4rem;
            }
            .destino-nombre {
                font-size: 2rem;
            }
        }

        @media (max-width: 768px) {
            .header-title {
                font-size: 1.5rem;
            }
            .turno-card {
                flex-direction: column;
                text-align: center;
                padding: 20px;
            }
            .turno-destino {
                align-items: center;
                text-align: center;
                margin-top: 20px;
            }
            .turno-flecha {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Botón para activar sonido -->
    <button id="btnActivarSonido" class="btn-sonido" onclick="activarSonido()">
        🔔 Activar Alertas de Sonido
    </button>

    <header class="header">
        <img src="{{ asset('images/logoClinic.png') }}" alt="Clínica Genezen">
        <h1 class="header-title">Sistema de Turnos</h1>
        <div class="current-time" id="currentTime">--:--:--</div>
    </header>

    <main class="main-content">
        <div class="turnos-container" id="turnosContainer">
            @if($turnosActivos->count() > 0)
                @foreach($turnosActivos as $index => $turno)
                    <div class="turno-card {{ $turno->turno_llamado_at && $turno->turno_llamado_at->diffInSeconds(now()) < 5 ? 'reciente' : '' }}" data-id="{{ $turno->id }}">
                        <div class="turno-numero">
                            <span class="turno-label">Turno</span>
                            <span class="turno-number">{{ $turno->numero_turno }}</span>
                        </div>
                        <span class="turno-flecha">→</span>
                        <div class="turno-destino">
                            <span class="destino-label">Dirigirse a</span>
                            <span class="destino-nombre">{{ $turno->destino ? $turno->destino->nombre : 'Sin destino' }}</span>
                            <span class="turno-hora">Llamado: {{ $turno->turno_llamado_at ? $turno->turno_llamado_at->format('H:i') : '--:--' }}</span>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state">
                    <div class="icon">🎫</div>
                    <h2>Sin turnos activos</h2>
                    <p>Los turnos llamados aparecerán aquí automáticamente</p>
                </div>
            @endif
        </div>
    </main>

    <footer class="footer">
        <p>Clínica Genezen - Sistema de Turnero</p>
    </footer>

    <!-- Audio de alerta -->
    <audio id="alertSound" preload="auto">
        <source src="{{ asset('sounds/alerta.mp3') }}" type="audio/mpeg">
    </audio>

    <script>
        // Configuración
        const config = {
            refreshInterval: {{ $config['refresh_interval'] ?? 3000 }},
            tiempoParpadeo: {{ $config['tiempo_parpadeo'] ?? 5000 }},
            sonidoActivo: {{ ($config['sonido_activo'] ?? 'true') === 'true' ? 'true' : 'false' }}
        };

        let sonidoActivado = false;
        let ultimosTurnos = [];

        // Activar sonido (requiere interacción del usuario)
        function activarSonido() {
            const audio = document.getElementById('alertSound');
            audio.play().then(() => {
                audio.pause();
                audio.currentTime = 0;
                sonidoActivado = true;
                document.getElementById('btnActivarSonido').classList.add('hidden');
                console.log('Sonido activado correctamente');
            }).catch(error => {
                console.log('Error activando sonido:', error);
                alert('No se pudo activar el sonido. Intenta de nuevo.');
            });
        }

        // Reproducir sonido de alerta
        function reproducirAlerta() {
            if (!sonidoActivado || !config.sonidoActivo) return;
            const audio = document.getElementById('alertSound');
            if (audio) {
                audio.currentTime = 0;
                audio.play().catch(error => {
                    console.log('No se pudo reproducir el sonido:', error);
                });
            }
        }

        // Actualizar reloj
        function actualizarReloj() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('es-CO', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });
            document.getElementById('currentTime').textContent = timeStr;
        }

        // Actualizar turnos
        function actualizarTurnos() {
            fetch('{{ route("turnero.api.activos") }}')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('turnosContainer');
                    const turnos = data.turnos;

                    // Detectar nuevos turnos
                    const nuevosIds = turnos.map(t => t.id);
                    const turnosNuevos = turnos.filter(t => t.es_reciente && !ultimosTurnos.includes(t.id));
                    
                    if (turnosNuevos.length > 0) {
                        reproducirAlerta();
                    }

                    ultimosTurnos = nuevosIds;

                    // Renderizar turnos
                    if (turnos.length === 0) {
                        container.innerHTML = `
                            <div class="empty-state">
                                <div class="icon">🎫</div>
                                <h2>Sin turnos activos</h2>
                                <p>Los turnos llamados aparecerán aquí automáticamente</p>
                            </div>
                        `;
                    } else {
                        container.innerHTML = turnos.map((turno, index) => `
                            <div class="turno-card ${turno.es_reciente ? 'reciente' : ''}" data-id="${turno.id}">
                                <div class="turno-numero">
                                    <span class="turno-label">Turno</span>
                                    <span class="turno-number">${turno.numero_turno}</span>
                                </div>
                                <span class="turno-flecha">→</span>
                                <div class="turno-destino">
                                    <span class="destino-label">Dirigirse a</span>
                                    <span class="destino-nombre">${turno.destino}</span>
                                    <span class="turno-hora">Llamado: ${turno.turno_llamado_at}</span>
                                </div>
                            </div>
                        `).join('');
                    }
                })
                .catch(error => console.error('Error actualizando turnos:', error));
        }

        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            actualizarReloj();
            setInterval(actualizarReloj, 1000);
            
            // Guardar turnos actuales
            document.querySelectorAll('.turno-card').forEach(card => {
                ultimosTurnos.push(parseInt(card.dataset.id));
            });

            // Actualizar turnos periódicamente
            setInterval(actualizarTurnos, config.refreshInterval);
        });
    </script>
</body>
</html>
