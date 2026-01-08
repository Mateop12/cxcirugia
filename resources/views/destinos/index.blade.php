@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold text-primary">⚙️ Gestionar Destinos</h1>
            <p class="text-muted mb-0">Configure las ubicaciones a las que se dirigen los pacientes</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('turnero.panel') }}" class="btn btn-outline-primary">← Volver al Panel</a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                + Nuevo Destino
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>Orden</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($destinos as $destino)
                        <tr>
                            <td>{{ $destino->orden }}</td>
                            <td><strong>{{ $destino->nombre }}</strong></td>
                            <td>{{ $destino->descripcion ?? '-' }}</td>
                            <td>
                                @if($destino->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-warning" 
                                        onclick="editDestino({{ json_encode($destino) }})">
                                    ✏️ Editar
                                </button>
                                <form action="{{ route('destinos.destroy', $destino) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('¿Eliminar este destino?')">
                                        🗑️ Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                No hay destinos configurados. Crea el primero.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Crear --}}
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('destinos.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Nuevo Destino</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" name="nombre" class="form-control" required 
                               placeholder="Ej: Consultorio 1, Quirófano A">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <input type="text" name="descripcion" class="form-control" 
                               placeholder="Descripción opcional">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Orden</label>
                        <input type="number" name="orden" class="form-control" value="0" 
                               placeholder="Orden de aparición">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="activo" value="1" class="form-check-input" id="createActivo" checked>
                        <label class="form-check-label" for="createActivo">Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Editar --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Editar Destino</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" name="nombre" id="editNombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <input type="text" name="descripcion" id="editDescripcion" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Orden</label>
                        <input type="number" name="orden" id="editOrden" class="form-control">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="activo" value="1" class="form-check-input" id="editActivo">
                        <label class="form-check-label" for="editActivo">Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editDestino(destino) {
        document.getElementById('editForm').action = `{{ url('destinos') }}/${destino.id}`;
        document.getElementById('editNombre').value = destino.nombre;
        document.getElementById('editDescripcion').value = destino.descripcion || '';
        document.getElementById('editOrden').value = destino.orden;
        document.getElementById('editActivo').checked = destino.activo;
        
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            new bootstrap.Modal(document.getElementById('createModal')).show();
        });
    @endif
</script>

<style>
    body {
        background-color: #f0f4f8;
    }
    
    .card {
        border-radius: 12px;
    }
    
    .table thead th {
        border: none;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
</style>
@endsection
