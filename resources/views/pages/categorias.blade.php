<head>
    <!-- Otros enlaces a hojas de estilo, si los tienes -->
    <link rel="stylesheet" href="{{ asset('assets/styleAggCategoria.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dashboard.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
</head>

@extends('welcome')

@section('title', 'Gestión de Categorías')

@section('content')

<script>
    window.bloquearBotonCategoria = function (formulario, textoCarga) {
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

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.modal').forEach(function (modal) {
            modal.addEventListener('show.bs.modal', function () {
                const formulario = modal.querySelector('form');

                if (formulario) {
                    formulario.dataset.enviando = '0';
                }

                const botonEditar = modal.querySelector('.btn-guardar-cambios-categoria');

                if (botonEditar) {
                    botonEditar.disabled = false;
                    botonEditar.style.pointerEvents = 'auto';
                    botonEditar.innerHTML = `
                        <i class="bi bi-check-circle me-1"></i>Guardar Cambios
                    `;
                }

                const botonAgregar = modal.querySelector('.btn-registrar-categoria');

                if (botonAgregar) {
                    botonAgregar.disabled = false;
                    botonAgregar.style.pointerEvents = 'auto';
                    botonAgregar.innerHTML = `
                        <i class="bi bi-check2-circle me-1"></i>Registrar Categoría
                    `;
                }
            });
        });
    });
</script>

<div class="container-fluid py-4">
    <h1 class="page-title mt-4">📋 Gestión de Categorías</h1>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Panel</a></li>
        <li class="breadcrumb-item active">Categorías</li>
    </ol>

    <!-- Botón para agregar nueva categoría -->
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregarCategoria">
            <i class="fas fa-plus-circle"></i> Añadir Categoría
        </button>
    </div>

    <!-- Modal para agregar categoría -->
    <div class="modal fade" id="modalAgregarCategoria" tabindex="-1" aria-labelledby="modalAgregarCategoriaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content shadow rounded-4 border-0">
                <div class="modal-header bg-primary text-white rounded-top-4" style="background-color: #6f42c1 !important;">
                    <h5 class="modal-title fw-bold d-flex align-items-center" id="modalAgregarCategoriaLabel">
                        <i class="bi bi-tags-fill me-2"></i>Nueva Categoría
                    </h5>

                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <form 
                        action="{{ route('categorias.store') }}" 
                        method="POST"
                        onsubmit="return bloquearBotonCategoria(this, 'Registrando...')">
                        @csrf

                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-semibold">Nombre de la categoría</label>

                            <input 
                                type="text" 
                                class="form-control rounded-pill px-4" 
                                id="nombre" 
                                name="nombre" 
                                required 
                                placeholder="Ej. Electrónica, Oficina, Higiene..."
                                style="text-transform: uppercase;">
                        </div>

                        <div class="d-flex justify-content-end">
                            <button 
                                type="submit" 
                                class="btn text-white rounded-pill px-4 btn-registrar-categoria" 
                                style="background-color: #6f42c1;">
                                <i class="bi bi-check2-circle me-1"></i>Registrar Categoría
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de categorías -->
    <div class="card shadow-sm">
        <div class="card-header text-white">
            <h5 class="mb-0">📦 Detalle de Categorías</h5>
        </div>

        <div class="card-body">

            <!-- Formulario de filtro de búsqueda -->
            <form id="busquedaForm" method="GET" action="{{ route('categorias') }}" class="mb-4">
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <input 
                            type="text" 
                            id="buscarInput" 
                            name="buscar" 
                            class="form-control form-control-lg" 
                            placeholder="Buscar categoria..." 
                            value="{{ request('buscar') }}">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Buscar
                    </button>
                </div>
            </form>
            
            @if ($categorias->isEmpty())
                <div class="alert alert-info text-center rounded-3 py-2 mt-4">
                    <div class="card-body py-2 d-flex flex-column align-items-center justify-content-center">
                        <i class="bi bi-info-circle me-2 move"></i> No hay Categorias registradas.
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
                            @foreach ($categorias as $categoria)
                                <tr>
                                    <td class="text-center" style="text-transform: uppercase;">
                                        {{ $categoria->nombre }}
                                    </td>

                                    <td class="text-center">
                                        <span class="{{ $categoria->estado == 1 ? 'estado-activo' : 'estado-inactivo' }}">
                                            {{ $categoria->estado == 1 ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <!-- Botón Editar -->
                                        <button 
                                            type="button"
                                            class="btn btn-warning btn-sm" 
                                            title="Editar" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editarCategoria{{ $categoria->id }}" 
                                            style="font-size: 14px; padding: 4px;">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <!-- Botón Activar/Desactivar -->
                                        <form 
                                            action="{{ $categoria->estado == 1 ? route('categoria.desactivar', $categoria->id) : route('categoria.activar', $categoria->id) }}" 
                                            method="POST" 
                                            class="d-inline-flex">
                                            @csrf
                                            @method('PUT')

                                            <button 
                                                type="submit" 
                                                class="btn btn-sm {{ $categoria->estado == 1 ? 'btn-danger' : 'btn-success' }}" 
                                                style="font-size: 14px; padding: 4px;">
                                                <i class="fas fa-toggle-{{ $categoria->estado == 1 ? 'off' : 'on' }}"></i>                                                
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- MODAL EDITAR CATEGORÍA -->
                                <div 
                                    class="modal fade" 
                                    id="editarCategoria{{ $categoria->id }}" 
                                    tabindex="-1" 
                                    aria-labelledby="editModalLabel{{ $categoria->id }}" 
                                    aria-hidden="true">

                                    <div class="modal-dialog modal-dialog-centered modal-md">
                                        <div class="modal-content shadow rounded-4 border-0">
                                            <div class="modal-header bg-primary text-white rounded-top-4">
                                                <h5 class="modal-title fw-bold" id="editModalLabel{{ $categoria->id }}">
                                                    <i class="bi bi-pencil-square me-2"></i>Editar Categoría
                                                </h5>

                                                <button 
                                                    type="button" 
                                                    class="btn-close btn-close-white" 
                                                    data-bs-dismiss="modal" 
                                                    aria-label="Cerrar">
                                                </button>
                                            </div>

                                            <div class="modal-body">
                                                <form 
                                                    action="{{ route('categorias.update', $categoria->id) }}" 
                                                    method="POST"
                                                    onsubmit="return bloquearBotonCategoria(this, 'Guardando cambios...')">
                                                    @csrf
                                                    @method('PUT')

                                                    <input type="hidden" name="id" value="{{ $categoria->id }}">
                                                    
                                                    <div class="mb-3">
                                                        <label for="nombre{{ $categoria->id }}" class="form-label fw-semibold">
                                                            Nombre de la categoría
                                                        </label>

                                                        <input 
                                                            type="text" 
                                                            class="form-control rounded-pill px-4" 
                                                            id="nombre{{ $categoria->id }}" 
                                                            name="nombre" 
                                                            value="{{ $categoria->nombre }}" 
                                                            required
                                                            style="text-transform: uppercase;">
                                                    </div>

                                                    <div class="d-flex justify-content-end">
                                                        <button 
                                                            type="submit" 
                                                            class="btn btn-success rounded-pill px-4 btn-guardar-cambios-categoria">
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

    <!-- Paginación con botones de siguiente y atrás -->
    <div class="row mt-4">
        <div class="col-12 col-md-6 mb-2 mb-md-0">
            <p class="mb-0">
                Mostrando {{ $categorias->firstItem() }} a {{ $categorias->lastItem() }} de {{ $categorias->total() }} categorias
            </p>
        </div>

        <div class="col-12 col-md-6">
            <div class="d-flex justify-content-md-end justify-content-center">
                {{ $categorias->appends(['buscar' => request('buscar')])->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div> 
</div>

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

@endsection