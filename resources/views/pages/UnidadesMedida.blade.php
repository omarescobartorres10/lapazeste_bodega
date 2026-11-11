@extends('welcome')

@section('title', 'Gestión de Tipo de Productos')

<head>
    <link rel="stylesheet" href="{{ asset('assets/styleAggCategoria.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dashboard.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <!-- jQuery necesario para el modal dinámico -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

@section('content')

<script>
    window.bloquearBotonUnidad = function (formulario, textoCarga) {
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
    <h1 class="page-title mt-4">⚖️ Gestión de Unidades de Medida</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Panel</a></li>
        <li class="breadcrumb-item active">Unidades de Medida</li>
    </ol>

    <!-- Botón para agregar nueva unidad -->
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregarUnidad">
            <i class="fas fa-plus-circle"></i> Añadir Unidad
        </button>
    </div>

    <!-- Modal para agregar unidad -->
    <div class="modal fade" id="modalAgregarUnidad" tabindex="-1" aria-labelledby="modalAgregarUnidadLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content shadow rounded-4 border-0">
                <div class="modal-header bg-success text-white rounded-top-4">
                    <h5 class="modal-title fw-bold d-flex align-items-center" id="modalAgregarUnidadLabel">
                        <i class="bi bi-plus-circle-fill me-2"></i>Nueva Unidad de Medida
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form 
                        action="{{ route('unidades.store') }}" 
                        method="POST"
                        onsubmit="return bloquearBotonUnidad(this, 'Guardando...')">
                        @csrf
                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-semibold">Nombre</label>
                            <input type="text" class="form-control rounded-pill px-4" name="nombre" placeholder="Ej. Kilogramo, Litro..." required style="text-transform: uppercase;">
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success rounded-pill px-4">
                                <i class="bi bi-check2-circle me-1"></i>Registrar Unidad
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de unidades de medida -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">📏 Detalle de Unidades de Medida</h5>
        </div>
        <div class="card-body">

            <!-- Formulario de búsqueda -->
            <form method="GET" action="{{ route('unidades') }}" class="mb-4">
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <input type="text" name="buscar" class="form-control form-control-lg" placeholder="Buscar Unidad..." value="{{ request('buscar') }}">
                    </div>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </div>
            </form>

            @if ($unidades->total() === 0)
                <div class="alert alert-info text-center rounded-3 py-2 mt-4">
                    <div class="card-body py-2 d-flex flex-column align-items-center justify-content-center">
                        <i class="bi bi-info-circle me-2 move"></i> No hay Unidades de Medida registradas.
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($unidades as $unidad)
                                <tr>
                                    <td class="text-center" style="text-transform: uppercase;">{{ $unidad->nombre }}</td>
                                    <td class="text-center">
                                        <span class="{{ $unidad->estado == 1 ? 'estado-activo' : 'estado-inactivo' }}">
                                            {{ $unidad->estado == 1 ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <!-- Botón Editar -->
                                        <button class="btn btn-warning btn-sm" title="Editar" data-bs-toggle="modal" data-bs-target="#editarUnidad{{ $unidad->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <!-- Botón Activar/Desactivar -->
                                        <form action="{{ $unidad->estado == 1 ? route('unidades.desactivar', $unidad->id) : route('unidades.activar', $unidad->id) }}" method="POST" class="d-inline-flex">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm {{ $unidad->estado == 1 ? 'btn-danger' : 'btn-success' }}">
                                                <i class="fas fa-toggle-{{ $unidad->estado == 1 ? 'off' : 'on' }}"></i>                                                
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal Editar Unidad -->
                                <div class="modal fade" id="editarUnidad{{ $unidad->id }}" tabindex="-1" aria-labelledby="editUnidadLabel{{ $unidad->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-md">
                                        <div class="modal-content shadow rounded-4 border-0">
                                            <div class="modal-header bg-primary text-white rounded-top-4">
                                                <h5 class="modal-title fw-bold" id="editUnidadLabel{{ $unidad->id }}">
                                                    <i class="bi bi-pencil-square me-2"></i>Editar Unidad de Medida
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form 
                                                    action="{{ route('unidades.update', $unidad->id) }}" 
                                                    method="POST"
                                                    onsubmit="return bloquearBotonUnidad(this, 'Guardando cambios...')">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Nombre</label>
                                                        <input type="text" class="form-control rounded-pill px-4" name="nombre" value="{{ $unidad->nombre }}" required style="text-transform: uppercase;">
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
            <p class="mb-0">Mostrando {{ $unidades->firstItem() }} a {{ $unidades->lastItem() }} de {{ $unidades->total() }} unidades</p>
        </div>
        <div class="col-12 col-md-6">
            <div class="d-flex justify-content-md-end justify-content-center">
                {{ $unidades->appends(['buscar' => request('buscar')])->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>  
</div>

<!-- Toast de notificación -->
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
</script>


{{-- @if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: '¡Error!',
        text: '{{ session("error") }}',
    });
</script>
@endif

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: '{{ session("success") }}',
    });
</script>
@endif --}}

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    document.getElementById('busquedaForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const buscar = document.getElementById('buscarInput').value;
        const form = e.target;

        // Hacer la petición GET manualmente
        fetch(form.action + '?buscar=' + encodeURIComponent(buscar))
            .then(response => response.text())
            .then(html => {
                // Reemplazar el contenido del cuerpo con la nueva vista
                document.body.innerHTML = html;

                // Quitar el parámetro de la URL
                window.history.pushState({}, '', '{{ route('tipoProducto') }}');
            });
    });
    // Quitar el parámetro de la URL
    window.history.pushState({}, '', '{{ route('tipoProducto') }}');
</script>

<script>
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const nombre = this.getAttribute('data-nombre');
        });
    });
</script>
@endpush