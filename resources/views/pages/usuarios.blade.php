<head>
    <link rel="stylesheet" href="{{ asset('assets/styleAggProduct.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dashboard.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

@extends('welcome')

@section('title', 'Gestión de Usuarios')

@section('content')

<script>
    window.bloquearBotonUsuario = function (formulario, textoCarga) {
        if (!formulario.checkValidity()) {
            return true;
        }

        if (formulario.dataset.enviando === '1') {
            return false;
        }

        formulario.dataset.enviando = '1';

        const boton = formulario.querySelector('button[type="submit"]');

        if (boton) {
            boton.disabled = true;
            boton.style.pointerEvents = 'none';

            boton.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                ${textoCarga}
            `;
        }

        return true;
    };
</script>

<div class="container-fluid py-4">
    <h1 class="page-title mt-4">👤 Gestión de Usuarios</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Panel</a></li>
        <li class="breadcrumb-item active">Usuarios</li>
    </ol>

    <!-- Botón para abrir modal -->
    <div class="d-flex mb-3">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregarUsuario">
            <i class="bi bi-person-plus"></i> Nuevo Usuario
        </button>
    </div>

    <!-- Modal AGREGAR USUARIO -->
    <div class="modal fade" id="modalAgregarUsuario" tabindex="-1" aria-labelledby="modalAgregarUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow rounded-4 border-0">
                <div class="modal-header text-white rounded-top-4" style="background-color: #198754;">
                    <h5 class="modal-title fw-bold d-flex align-items-center">
                        <i class="bi bi-person-plus me-2"></i>Agregar Nuevo Usuario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <form 
                        method="POST" 
                        action="{{ route('usuarios.store') }}"
                        onsubmit="return bloquearBotonUsuario(this, 'Guardando...')">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Nombre completo</label>
                                <input type="text" name="nombre" class="form-control rounded-pill px-4" required placeholder="Ejemplo: Juan Pérez" style="text-transform: uppercase;">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Usuario</label>
                                <input type="text" name="usuario" class="form-control rounded-pill px-4" required placeholder="Ejemplo: jperez">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Contraseña</label>
                                <input type="password" name="password" class="form-control rounded-pill px-4" required placeholder="Mínimo 4 caracteres">
                            </div>

                            <div class="col-md-12 mb-3" id="bodegaSelectContainer" style="display: none;">
                                <label class="form-label fw-semibold">Asignar Bodega</label>
                                <select name="bodega_id" class="form-select rounded-pill px-4">
                                    <option value="">Seleccionar bodega</option>
                                    @foreach ($bodegas as $bodega)
                                        <option value="{{ $bodega->id }}">{{ $bodega->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn text-white rounded-pill px-4" style="background-color: #198754;">
                                <i class="bi bi-save me-1"></i> Guardar Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- FIN MODAL AGREGAR -->

    <!-- Tabla de usuarios -->
    <div class="card shadow-sm">
        <div class="card-header text-white" style="background-color: #198754;">
            <h5 class="mb-0">📋 Lista de Usuarios</h5>
        </div>
        <div class="card-body">
            @if ($usuarios->isEmpty())
                <div class="alert alert-info text-center rounded-3 py-2 mt-4">
                    <i class="bi bi-info-circle me-2"></i> No hay usuarios registrados.
                </div>
            @else
                <div class="table-responsive">
                    <table class="custom-table text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Bodega</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($usuarios as $usuario)
                                <tr>
                                    <td>{{ $usuario->nombre }}</td>
                                    <td>{{ $usuario->usuario }}</td>
                                    <td>{{ $usuario->bodega->nombre ?? '—' }}</td>
                                    <td>
                                        <span class="{{ $usuario->estado ? 'estado-activo' : 'estado-inactivo' }}">
                                            {{ $usuario->estado ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <!-- Editar -->
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editarUsuario{{ $usuario->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <!-- Activar/Desactivar -->
                                            <form action="{{ route('usuarios.toggle', $usuario->id) }}" method="POST" class="form-toggle">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-sm {{ $usuario->estado ? 'btn-danger' : 'btn-success' }}">
                                                    <i class="bi bi-toggle-{{ $usuario->estado ? 'off' : 'on' }}"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Editar -->
                                <div class="modal fade" id="editarUsuario{{ $usuario->id }}" tabindex="-1" aria-labelledby="editarUsuarioLabel{{ $usuario->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content shadow rounded-4 border-0">
                                            <div class="modal-header text-white rounded-top-4" style="background-color: #198754;">
                                                <h5 class="modal-title fw-bold">
                                                    <i class="bi bi-pencil-square me-2"></i>Editar Usuario
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                            </div>

                                            <div class="modal-body">
                                                <form 
                                                    method="POST" 
                                                    action="{{ route('usuarios.update', $usuario->id) }}"
                                                    onsubmit="return bloquearBotonUsuario(this, 'Guardando cambios...')">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label fw-semibold">Nombre</label>
                                                            <input type="text" name="nombre" class="form-control rounded-pill px-4" value="{{ $usuario->nombre }}" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label fw-semibold">Usuario</label>
                                                            <input type="text" name="usuario" class="form-control rounded-pill px-4" value="{{ $usuario->usuario }}" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label fw-semibold">Rol</label>
                                                            <select name="rol" class="form-select rounded-pill px-4 rol-select" required>
                                                                <option value="admin" {{ $usuario->rol == 'admin' ? 'selected' : '' }}>Administrador</option>
                                                                <option value="encargado" {{ $usuario->rol == 'encargado' ? 'selected' : '' }}>Encargado</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label fw-semibold">Contraseña (opcional)</label>
                                                            <input type="password" name="password" class="form-control rounded-pill px-4" placeholder="Dejar en blanco para no cambiar">
                                                        </div>
                                                        <div class="col-md-12 mb-3">
                                                            <label class="form-label fw-semibold">Asignar Bodega (si es encargado)</label>
                                                            <select name="bodega_id" class="form-select rounded-pill px-4">
                                                                <option value="">Seleccionar bodega</option>
                                                                @foreach ($bodegas as $bodega)
                                                                    <option value="{{ $bodega->id }}" {{ $usuario->bodega_id == $bodega->id ? 'selected' : '' }}>
                                                                        {{ $bodega->nombre }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <button type="submit" class="btn text-white rounded-pill px-4" style="background-color: #198754;">
                                                            <i class="bi bi-save me-1"></i> Guardar Cambios
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $usuarios->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Mostrar/ocultar campo de bodega según rol
document.getElementById('rolSelect').addEventListener('change', function() {
    const bodegaContainer = document.getElementById('bodegaSelectContainer');
    bodegaContainer.style.display = this.value === 'encargado' ? 'block' : 'none';
});

// Confirmación activar/desactivar
document.querySelectorAll('.form-toggle').forEach(form => {
    form.addEventListener('submit', e => {
        e.preventDefault();
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Este cambio modificará el estado del usuario.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) form.submit();
        });
    });
});

// Notificaciones animadas
document.addEventListener('DOMContentLoaded', () => {
    @if(session('success'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            showClass: { popup: 'animate__animated animate__fadeInDown' },
            hideClass: { popup: 'animate__animated animate__fadeOutUp' }
        });
    @endif
});
</script>
@endpush