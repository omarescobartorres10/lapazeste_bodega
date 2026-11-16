@extends('welcome')

@section('title', 'Gestión de Usuarios')

@section('content')
<div class="container-fluid py-4">
    <h1 class="page-title mt-4">👤 Gestión de Usuarios</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Panel</a></li>
        <li class="breadcrumb-item active">Usuarios</li>
    </ol>

    <!-- Botón para abrir modal nuevo usuario -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <button class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalAgregarUsuario">
            <i class="bi bi-person-plus me-1"></i> Nuevo Usuario
        </button>
    </div>

    <!-- Tabla de usuarios -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header text-white rounded-top-4" style="background-color: #198754;">
            <h5 class="mb-0 fw-semibold"><i class="bi bi-people me-2"></i>Lista de Usuarios</h5>
        </div>
        <div class="card-body">
            @if ($usuarios->isEmpty())
                <div class="alert alert-info text-center rounded-3 py-3 mt-2">
                    <i class="bi bi-info-circle me-2 fs-5"></i> No hay usuarios registrados.
                </div>
            @else
                <div class="table-responsive">
                    <table class="custom-table text-center align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Bodega Asignada</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($usuarios as $usuario)
                                <tr>
                                    <td class="fw-semibold">{{ $usuario->nombre }}</td>
                                    <td class="fw-bold">{{ $usuario->usuario }}</td>
                                    <td>
                                        <span class="badge {{ $usuario->rol === 'admin' ? 'bg-primary' : 'bg-success' }} px-3 py-2 rounded-pill">
                                            <i class="bi {{ $usuario->rol === 'admin' ? 'bi-shield-check' : 'bi-shop' }} me-1"></i>
                                            {{ $usuario->rol === 'admin' ? 'Administrador' : 'Encargado' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($usuario->rol === 'admin')
                                            <span class="text-muted">Todas (Global)</span>
                                        @else
                                            <span class="fw-semibold text-success">
                                                <i class="bi bi-building me-1"></i>{{ $usuario->bodega->nombre ?? 'Sin asignar' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="{{ $usuario->estado ? 'estado-activo' : 'estado-inactivo' }}">
                                            {{ $usuario->estado ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <!-- Editar -->
                                            <button class="btn btn-warning btn-sm rounded-circle shadow-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editarUsuario{{ $usuario->id }}"
                                                    title="Editar usuario">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <!-- Activar/Desactivar -->
                                            @if(Auth::id() !== $usuario->id)
                                                <form action="{{ route('usuarios.toggle', $usuario->id) }}" method="POST" class="form-toggle d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" 
                                                            class="btn btn-sm rounded-circle shadow-sm {{ $usuario->estado ? 'btn-danger' : 'btn-success' }}"
                                                            title="{{ $usuario->estado ? 'Desactivar usuario' : 'Activar usuario' }}">
                                                        <i class="bi bi-toggle-{{ $usuario->estado ? 'off' : 'on' }}"></i>
                                                    </button>
                                                </form>

                                                <!-- Eliminar -->
                                                <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" class="form-eliminar d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-outline-danger btn-sm rounded-circle shadow-sm"
                                                            title="Eliminar usuario">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="badge bg-light text-muted border px-2 py-1" title="Sesión activa">Actual</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $usuarios->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- ======================================================= -->
<!-- MODAL: AGREGAR USUARIO -->
<!-- ======================================================= -->
<div class="modal fade" id="modalAgregarUsuario" tabindex="-1" aria-labelledby="modalAgregarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow rounded-4 border-0">
            <div class="modal-header text-white rounded-top-4" style="background-color: #198754;">
                <h5 class="modal-title fw-bold d-flex align-items-center" id="modalAgregarUsuarioLabel">
                    <i class="bi bi-person-plus me-2"></i>Agregar Nuevo Usuario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">
                <form id="formAgregarUsuario" method="POST" action="{{ route('usuarios.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control rounded-pill px-4" required 
                                   placeholder="Ejemplo: JUAN PÉREZ" style="text-transform: uppercase;" value="{{ old('nombre') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre de usuario <span class="text-danger">*</span></label>
                            <input type="text" name="usuario" class="form-control rounded-pill px-4" required 
                                   placeholder="Ejemplo: jperez" style="text-transform: lowercase;" value="{{ old('usuario') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control rounded-pill px-4" required 
                                   placeholder="Mínimo 4 caracteres">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Rol de usuario <span class="text-danger">*</span></label>
                            <select name="rol" id="rolSelectAgregar" class="form-select rounded-pill px-4" required>
                                <option value="admin" {{ old('rol') === 'admin' ? 'selected' : '' }}>Administrador (Acceso total)</option>
                                <option value="encargado" {{ old('rol') === 'encargado' ? 'selected' : '' }}>Encargado de Bodega</option>
                            </select>
                        </div>

                        <div class="col-md-12" id="bodegaSelectContainerAgregar" style="display: {{ old('rol') === 'encargado' ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold">Bodega asignada <span class="text-danger">*</span></label>
                            <select name="bodega_id" id="bodegaSelectAgregar" class="form-select rounded-pill px-4" {{ old('rol') === 'encargado' ? 'required' : '' }}>
                                <option value="">-- Seleccionar bodega --</option>
                                @foreach ($bodegas as $bodega)
                                    <option value="{{ $bodega->id }}" {{ old('bodega_id') == $bodega->id ? 'selected' : '' }}>
                                        {{ $bodega->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted ps-3">El encargado solo podrá gestionar artículos y movimientos de esta bodega.</small>
                        </div>
                    </div>

                    <div class="text-end mt-4 pt-2 border-top">
                        <button type="button" class="btn btn-secondary rounded-pill px-4 me-2" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success text-white rounded-pill px-4">
                            <i class="bi bi-save me-1"></i> Guardar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================= -->
<!-- MODALES: EDITAR USUARIO (FUERA DE LA TABLA) -->
<!-- ======================================================= -->
@foreach ($usuarios as $usuario)
<div class="modal fade" id="editarUsuario{{ $usuario->id }}" tabindex="-1" aria-labelledby="editarUsuarioLabel{{ $usuario->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow rounded-4 border-0">
            <div class="modal-header text-white rounded-top-4" style="background-color: #198754;">
                <h5 class="modal-title fw-bold" id="editarUsuarioLabel{{ $usuario->id }}">
                    <i class="bi bi-pencil-square me-2"></i>Editar Usuario: {{ $usuario->nombre }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">
                <form method="POST" action="{{ route('usuarios.update', $usuario->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control rounded-pill px-4" value="{{ $usuario->nombre }}" required style="text-transform: uppercase;">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre de usuario <span class="text-danger">*</span></label>
                            <input type="text" name="usuario" class="form-control rounded-pill px-4" value="{{ $usuario->usuario }}" required style="text-transform: lowercase;">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Rol de usuario <span class="text-danger">*</span></label>
                            <select name="rol" class="form-select rounded-pill px-4 edit-rol-select" data-user-id="{{ $usuario->id }}" required>
                                <option value="admin" {{ $usuario->rol === 'admin' ? 'selected' : '' }}>Administrador (Acceso total)</option>
                                <option value="encargado" {{ $usuario->rol === 'encargado' ? 'selected' : '' }}>Encargado de Bodega</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contraseña nueva</label>
                            <input type="password" name="password" class="form-control rounded-pill px-4" placeholder="Dejar en blanco para conservar la actual">
                            <small class="text-muted ps-3">Solo complete si desea cambiarla.</small>
                        </div>

                        <div class="col-md-12 edit-bodega-container" id="editBodegaContainer{{ $usuario->id }}" 
                             style="display: {{ $usuario->rol === 'encargado' ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold">Bodega asignada <span class="text-danger">*</span></label>
                            <select name="bodega_id" class="form-select rounded-pill px-4 edit-bodega-select" 
                                    id="editBodegaSelect{{ $usuario->id }}" 
                                    {{ $usuario->rol === 'encargado' ? 'required' : '' }}>
                                <option value="">-- Seleccionar bodega --</option>
                                @foreach ($bodegas as $bodega)
                                    <option value="{{ $bodega->id }}" {{ $usuario->bodega_id == $bodega->id ? 'selected' : '' }}>
                                        {{ $bodega->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted ps-3">El encargado solo podrá gestionar artículos y movimientos de esta bodega.</small>
                        </div>
                    </div>

                    <div class="text-end mt-4 pt-2 border-top">
                        <button type="button" class="btn btn-secondary rounded-pill px-4 me-2" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success text-white rounded-pill px-4">
                            <i class="bi bi-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    // =========================================================
    // 1. Alternar selector de bodega en modal AGREGAR
    // =========================================================
    const rolSelectAgregar = document.getElementById('rolSelectAgregar');
    const bodegaContainerAgregar = document.getElementById('bodegaSelectContainerAgregar');
    const bodegaSelectAgregar = document.getElementById('bodegaSelectAgregar');

    if (rolSelectAgregar && bodegaContainerAgregar && bodegaSelectAgregar) {
        rolSelectAgregar.addEventListener('change', function() {
            if (this.value === 'encargado') {
                bodegaContainerAgregar.style.display = 'block';
                bodegaSelectAgregar.required = true;
            } else {
                bodegaContainerAgregar.style.display = 'none';
                bodegaSelectAgregar.required = false;
                bodegaSelectAgregar.value = '';
            }
        });
    }

    // =========================================================
    // 2. Alternar selector de bodega en modales EDITAR
    // =========================================================
    document.querySelectorAll('.edit-rol-select').forEach(select => {
        select.addEventListener('change', function() {
            const userId = this.getAttribute('data-user-id');
            const container = document.getElementById('editBodegaContainer' + userId);
            const bodegaSelect = document.getElementById('editBodegaSelect' + userId);

            if (container && bodegaSelect) {
                if (this.value === 'encargado') {
                    container.style.display = 'block';
                    bodegaSelect.required = true;
                } else {
                    container.style.display = 'none';
                    bodegaSelect.required = false;
                    bodegaSelect.value = '';
                }
            }
        });
    });

    // =========================================================
    // 3. Confirmación SweetAlert para Activar/Desactivar
    // =========================================================
    document.querySelectorAll('.form-toggle').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Modificar estado?',
                text: 'Esta acción cambiará el acceso del usuario al sistema.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // =========================================================
    // 4. Confirmación SweetAlert para Eliminar
    // =========================================================
    document.querySelectorAll('.form-eliminar').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Eliminar usuario?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // =========================================================
    // 5. Notificaciones de Éxito / Error / Validación
    // =========================================================
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Operación exitosa!',
            text: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Atención',
            text: "{{ session('error') }}"
        });
    @endif

    @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Revisa los siguientes campos:',
            html: '<ul style="text-align: left; margin: 0; padding-left: 20px;">' +
                  @json($errors->all()).map(e => `<li>${e}</li>`).join('') +
                  '</ul>'
        });
    @endif

});
</script>
@endpush
