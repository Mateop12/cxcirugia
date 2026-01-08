@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="text-center mb-5">
                <h1 class="display-5 fw-bold text-primary">🏥 Módulo de Admisión</h1>
                <p class="lead text-muted">Busque un paciente existente o registre uno nuevo para asignar turno</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Buscador --}}
            <div class="card shadow-lg border-0 mb-4">
                <div class="card-body p-4">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-end-0">🔍</span>
                        <input type="text" id="searchInput" class="form-control border-start-0" 
                               placeholder="Buscar por cédula, nombre o apellido..." autofocus>
                    </div>
                </div>
            </div>

            {{-- Resultados --}}
            <div id="searchResults" class="mb-4" style="display: none;">
                <h5 class="text-muted mb-3">Resultados de búsqueda</h5>
                <div class="list-group shadow-sm" id="resultsList">
                    {{-- Los resultados se inyectan aquí vía JS --}}
                </div>
            </div>

            {{-- Botón Nuevo Paciente --}}
            <div class="text-center mt-4">
                <p class="text-muted mb-3">¿No encuentra al paciente?</p>
                <a href="{{ route('admision.create') }}" class="btn btn-lg btn-success px-5 rounded-pill shadow">
                    + Registrar Nuevo Paciente
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    const searchInput = document.getElementById('searchInput');
    const resultsContainer = document.getElementById('searchResults');
    const resultsList = document.getElementById('resultsList');
    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();

        if (query.length < 3) {
            resultsContainer.style.display = 'none';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`{{ route('admision.buscar') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ query: query })
            })
            .then(response => response.json())
            .then(data => {
                resultsList.innerHTML = '';
                
                if (data.length > 0) {
                    resultsContainer.style.display = 'block';
                    data.forEach(paciente => {
                        const item = document.createElement('div');
                        item.className = 'list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center';
                        
                        let actionButton = '';
                        if (paciente.tiene_turno_hoy) {
                            actionButton = `<span class="badge bg-secondary fs-6">Turno #${paciente.numero_turno}</span>`;
                        } else {
                            actionButton = `<button class="btn btn-primary btn-sm rounded-pill px-3" onclick="asignarTurno(${paciente.id})">🎫 Asignar Turno</button>`;
                        }

                        item.innerHTML = `
                            <div>
                                <h5 class="mb-1 fw-bold">${paciente.nombre}</h5>
                                <p class="mb-0 text-muted">ID: ${paciente.identificacion}</p>
                            </div>
                            <div>${actionButton}</div>
                        `;
                        resultsList.appendChild(item);
                    });
                } else {
                    resultsContainer.style.display = 'none';
                }
            });
        }, 300);
    });

    function asignarTurno(pacienteId) {
        if (!confirm('¿Asignar turno a este paciente?')) return;

        fetch(`{{ url('admision/asignar-turno') }}/${pacienteId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                searchInput.value = ''; // Limpiar búsqueda
                resultsContainer.style.display = 'none'; // Ocultar resultados
                location.reload(); // Recargar para ver mensaje flash si hubiera o actualizar estado
            }
        })
        .catch(error => alert('Error asignando turno'));
    }
</script>

<style>
    .form-control:focus {
        box-shadow: none;
        border-color: #ced4da;
    }
    .input-group-text {
        color: #6c757d;
    }
</style>
@endsection
