<head>
    <link rel="stylesheet" href="{{ asset('assets/styleAggCategoria.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dashboard.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
</head>

@extends('welcome')

@section('title', 'Gestión de Departamentos')

@section('content')

<script>
    window.bloquearBotonDepartamento = function (formulario, textoCarga) {
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
    <h1 class="page-title mt-4">🏢 Gestión de Departamentos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Panel</a></li>
        <li class="breadcrumb-item active">Departamentos</li>
    </ol>

    <!-- Botón para agregar nuevo departamento -->
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregarDepartamento">
            <i class="bi bi-building-add"></i> Añadir Departamento
        </button>
    </div>

    <!-- Modal para agregar departamento -->
    <div class="modal fade" id="modalAgregarDepartamento" tabindex="-1" aria-labelledby="modalAgregarDepartamentoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content shadow rounded-4 border-0">
                <div class="modal-header bg-primary text-white rounded-top-4" style="background-color: #6f42c1 !important;">
                    <h5 class="modal-title fw-bold d-flex align-items-center" id="modalAgregarDepartamentoLabel">
                        <i class="bi bi-building-fill-add me-2"></i>Nuevo Departamento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form 
                        action="{{ route('departamentos.store') }}" 
                        method="POST"
                        onsubmit="return bloquearBotonDepartamento(this, 'Guardando...')">
                        @csrf

                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-semibold">Nombre del Departamento</label>
                            <input 
                                type="text" 
                                class="form-control rounded-pill px-4" 
                                id="nombre" 
                                name="nombre" 
                                required 
                                placeholder="Ej. Finanzas, Recursos Humanos, Informática..."
                                style="text-transform: uppercase;">
                        </div>

                        <div class="mb-3">
                            <label for="encargado" class="form-label fw-semibold">Encargado</label>
                            <input 
                                type="text" 
                                class="form-control rounded-pill px-4" 
                                id="encargado" 
                                name="encargado" 
                                placeholder="Nombre del encargado"
                                style="text-transform: uppercase;">
                        </div>

                        <div class="mb-3">
                            <label for="estado" class="form-label fw-semibold">Estado</label>
                            <select class="form-select rounded-pill px-4" name="estado" id="estado">
                                <option value="Activo" selected>Activo</option>
                                <option value="Inactivo">Inactivo</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn text-white rounded-pill px-4" style="background-color: #6f42c1;">
                                <i class="bi bi-check2-circle me-1"></i>Registrar Departamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de departamentos -->
    <div class="card shadow-sm">
        <div class="card-header text-white">
            <h5 class="mb-0">🏢 Detalle de Departamentos</h5>
        </div>
        <div class="card-body">

            <!-- Formulario de filtro de búsqueda -->
            <form id="busquedaForm" method="GET" action="{{ route('departamentos') }}" class="mb-4">
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <input type="text" id="buscarInput" name="buscar" class="form-control form-control-lg" placeholder="Buscar departamento..." value="{{ request('buscar') }}">
                    </div>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </div>
            </form>
            
            @if ($departamentos->isEmpty())
                <div class="alert alert-info text-center rounded-3 py-2 mt-4">
                    <div class="card-body py-2 d-flex flex-column align-items-center justify-content-center">
                        <i class="bi bi-info-circle me-2 move"></i> No hay Departamentos registrados.
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Nombre</th>
                                <th>Encargado</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($departamentos as $departamento)
                                <tr>
                                    <td class="text-center" style="text-transform: uppercase;">{{ $departamento->nombre }}</td>
                                    <td class="text-center" style="text-transform: uppercase;">{{ $departamento->encargado ?? '—' }}</td>
                                    <td class="text-center">
                                        <span class="{{ $departamento->estado == 'Activo' ? 'estado-activo' : 'estado-inactivo' }}">
                                            {{ $departamento->estado }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <!-- Botón Editar -->
                                        <button class="btn btn-warning btn-sm" title="Editar" data-bs-toggle="modal" data-bs-target="#editarDepartamento{{ $departamento->id }}" style="font-size: 14px; padding: 4px;">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <!-- Botón Eliminar -->
                                        <form action="{{ route('departamentos.destroy', $departamento->id) }}" method="POST" class="form-eliminar d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i> Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- MODAL EDITAR DEPARTAMENTO -->
                                <div class="modal fade" id="editarDepartamento{{ $departamento->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $departamento->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-md">
                                        <div class="modal-content shadow rounded-4 border-0">
                                            <div class="modal-header bg-primary text-white rounded-top-4">
                                                <h5 class="modal-title fw-bold" id="editModalLabel{{ $departamento->id }}">
                                                    <i class="bi bi-pencil-square me-2"></i>Editar Departamento
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form 
                                                    action="{{ route('departamentos.update', $departamento->id) }}" 
                                                    method="POST"
                                                    onsubmit="return bloquearBotonDepartamento(this, 'Guardando cambios...')">
                                                    @csrf
                                                    @method('PUT')
                                                    
                                                    <div class="mb-3">
                                                        <label for="nombre{{ $departamento->id }}" class="form-label fw-semibold">Nombre</label>
                                                        <input 
                                                            type="text" 
                                                            class="form-control rounded-pill px-4" 
                                                            id="nombre{{ $departamento->id }}" 
                                                            name="nombre" 
                                                            value="{{ $departamento->nombre }}" 
                                                            required
                                                            style="text-transform: uppercase;">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="encargado{{ $departamento->id }}" class="form-label fw-semibold">Encargado</label>
                                                        <input 
                                                            type="text" 
                                                            class="form-control rounded-pill px-4" 
                                                            id="encargado{{ $departamento->id }}" 
                                                            name="encargado" 
                                                            value="{{ $departamento->encargado }}"
                                                            style="text-transform: uppercase;">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="estado{{ $departamento->id }}" class="form-label fw-semibold">Estado</label>
                                                        <select class="form-select rounded-pill px-4" name="estado">
                                                            <option value="Activo" {{ $departamento->estado == 'Activo' ? 'selected' : '' }}>Activo</option>
                                                            <option value="Inactivo" {{ $departamento->estado == 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                                                        </select>
                                                    </div>

                                                    <div class="d-flex justify-content-end">
                                                        <button type="submit" class="btn btn-success rounded-pill px-4">
                                                            <i class="bi bi-check-circle me-1"></i>Guardar Cambios
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
            @endif
        </div>
    </div>

    <!-- Paginación -->
    <div class="row mt-4">
        <div class="col-12 col-md-6 mb-2 mb-md-0">
            <p class="mb-0">Mostrando {{ $departamentos->firstItem() }} a {{ $departamentos->lastItem() }} de {{ $departamentos->total() }} departamentos</p>
        </div>
        <div class="col-12 col-md-6">
            <div class="d-flex justify-content-md-end justify-content-center">
                {{ $departamentos->appends(['buscar' => request('buscar')])->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Toasts -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1055;">
    @if(session('success'))
        <div class="custom-toast">
            <i class="bi bi-check-circle-fill toast-icon"></i>
            <span class="toast-text">{{ session('success') }}</span>
            <button class="toast-close" data-bs-dismiss="toast" aria-label="Cerrar">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="custom-toast error">
            <i class="bi bi-exclamation-triangle-fill toast-icon"></i>
            <span class="toast-text">{{ session('error') }}</span>
            <button class="toast-close" data-bs-dismiss="toast" aria-label="Cerrar">&times;</button>
        </div>
    @endif
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            document.querySelectorAll('.custom-toast').forEach(toast => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 500);
            });
        }, 4000);
    });

    document.getElementById('busquedaForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const buscar = document.getElementById('buscarInput').value;
        const form = e.target;

        fetch(form.action + '?buscar=' + encodeURIComponent(buscar))
            .then(response => response.text())
            .then(html => {
                document.body.innerHTML = html;
                window.history.pushState({}, '', '{{ route('departamentos') }}');
            });
    });
    window.history.pushState({}, '', '{{ route('departamentos') }}');
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Seleccionar todos los formularios con clase "form-eliminar"
    const forms = document.querySelectorAll('.form-eliminar');

    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // Detiene el envío automático del formulario

            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡Este departamento se eliminará permanentemente!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Envía el formulario si el usuario confirma
                }
            });
        });
    });
});
</script>

@endpush