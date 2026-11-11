<head>
    <link rel="stylesheet" href="{{ asset('assets/styleAggProduct.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dashboard.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

@extends('welcome')

@section('title', 'Solicitantes')

@section('content')
<div class="container-fluid py-4">

    {{-- Toast de éxito --}}
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            });
        </script>
    @endif

    <h1 class="page-title mt-4">👤 Gestión de Solicitantes</h1>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Panel</a></li>
        <li class="breadcrumb-item active">Solicitantes</li>
    </ol>

    {{-- Botones superiores --}}
    <div class="d-flex mb-3 gap-2">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregarSolicitante">
            <i class="bi bi-person-plus"></i> Nuevo Solicitante
        </button>

        <!-- <a href="{{ route('solicitantes.export.excel') }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel"></i> Excel
        </a> -->

        <a href="{{ route('solicitantes.export.pdf') }}" class="btn btn-outline-danger">
            <i class="bi bi-file-earmark-pdf"></i> PDF
        </a>
    </div>

    {{-- BUSCADOR Y FILTROS --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form id="formFiltroSolicitantes" method="GET" action="{{ route('solicitantes.index') }}">
                <div class="row g-3 align-items-center">

                    <div class="col-md-5">
                        <label class="form-label fw-semibold mb-1">Buscar</label>
                        <input type="text" name="search" class="form-control"
                            placeholder="DUI, nombre o departamento"
                            value="{{ request('search') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold mb-1">Departamento</label>
                        <select name="departamento_id" class="form-select" id="selectDepartamentoFiltro">
                            <option value="">Todos los departamentos</option>
                            @foreach($departamentos as $dep)
                                <option value="{{ $dep->id }}" {{ request('departamento_id') == $dep->id ? 'selected' : '' }}>
                                    {{ strtoupper($dep->nombre) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold mb-1 invisible">x</label>
                        <button class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- TABLA --}}
    <div class="card shadow-sm">
        <div class="card-header text-white" style="background-color: #198754;">
            <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i> Lista de Solicitantes</h5>
        </div>

        <div class="card-body p-0">

            @if($solicitantes->count() == 0)
                <div class="text-center p-4 text-muted">
                    <i class="bi bi-info-circle fa-2x mb-2"></i>
                    <p class="mb-0">No se encontraron solicitantes.</p>
                </div>
            @else

            <div class="table-responsive">
                <table class="custom-table text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>DUI</th>
                            <th>Nombre</th>
                            <th>Cargo</th>
                            <th>Departamento</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Estado</th>
                            <th>Solicitudes</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($solicitantes as $s)
                            <tr>
                                <td>{{ $s->dui }}</td>
                                <td class="fw-semibold">{{ strtoupper($s->nombre) }}</td>
                                <td>{{ $s->cargo }}</td>
                                <td>{{ strtoupper($s->departamento->nombre ?? '—') }}</td>
                                <td>{{ $s->telefono }}</td>
                                <td>{{ $s->correo }}</td>

                                <td>
                                    @if($s->estado === 'activo')
                                        <span class="estado-activo">Activo</span>
                                    @else
                                        <span class="estado-inactivo">Inactivo</span>
                                    @endif
                                </td>

                                <td><span class="badge bg-primary">{{ $s->solicitudes_count }}</span></td>

                                <td>
                                    <div class="d-flex justify-content-center gap-2">

                                        {{-- EDITAR --}}
                                        <button class="btn btn-warning btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditar{{ $s->id }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        {{-- ELIMINAR --}}
                                        <button class="btn btn-danger btn-sm btn-delete" data-id="{{ $s->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>

                                        {{-- DELETE FORM --}}
                                        <form id="deleteForm-{{ $s->id }}" method="POST"
                                            action="{{ route('solicitantes.destroy', $s->id) }}"
                                            style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>

                                    </div>
                                </td>
                            </tr>

                            {{-- MODAL EDITAR --}}
                            @include('admin.solicitantes.modal_editar', ['s' => $s, 'departamentos' => $departamentos])
                        @endforeach
                    </tbody>

                </table>
            </div>

            {{-- PAGINACIÓN --}}
            <div class="row mt-4 px-3">
                <div class="col-12 col-md-6 mb-2 mb-md-0">
                    <p class="text-muted">
                        Mostrando <strong>{{ $solicitantes->firstItem() }}</strong> a
                        <strong>{{ $solicitantes->lastItem() }}</strong> de
                        <strong>{{ $solicitantes->total() }}</strong> solicitantes
                    </p>
                </div>

                <div class="col-12 col-md-6">
                    <div class="d-flex justify-content-md-end justify-content-center">
                        {{ $solicitantes->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>

            @endif

        </div>
    </div>
</div>

@include('admin.solicitantes.modal_agregar')

@endsection


@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    // =====================================================
    // AUTO-FILTRAR POR DEPARTAMENTO AL CAMBIAR EL SELECT
    // =====================================================
    document.getElementById('selectDepartamentoFiltro')
        ?.addEventListener('change', () => document.getElementById('formFiltroSolicitantes').submit());

    // =====================================================
    // DUI → FORMATO 00000000-0
    // =====================================================
    function formatearDui(input) {
        let v = input.value.replace(/\D/g, '').slice(0, 9);
        input.value = v.length > 8 ? v.slice(0,8) + '-' + v.slice(8) : v;
    }
    document.querySelectorAll('.input-dui').forEach(inp =>
        inp.addEventListener('input', () => formatearDui(inp))
    );

    // =====================================================
    // TELÉFONO → FORMATO 0000-0000
    // =====================================================
    function formatearTel(input) {
        let v = input.value.replace(/\D/g, '').slice(0, 8);
        input.value = v.length > 4 ? v.slice(0,4) + '-' + v.slice(4) : v;
    }
    document.querySelectorAll('.input-telefono').forEach(inp =>
        inp.addEventListener('input', () => formatearTel(inp))
    );

    // =====================================================
    // CÓDIGO DE MARCADOR → SOLO NÚMEROS (SIN LÍMITE)
    // =====================================================
    document.querySelectorAll('.input-marcador').forEach(inp =>
        inp.addEventListener('input', () => inp.value = inp.value.replace(/\D/g, ''))
    );

    // =====================================================
    // VALIDACIÓN ANTES DE ENVIAR FORM (SweetAlert)
    // =====================================================
    document.querySelectorAll('.form-solicitante').forEach(form => {
        form.addEventListener('submit', function(e) {

            const dui = this.querySelector('input[name="dui"]').value;
            const tel = this.querySelector('input[name="telefono"]').value;
            const correo = this.querySelector('input[name="correo"]').value;

            let errores = [];

            if (!/^\d{8}-\d{1}$/.test(dui)) errores.push('El DUI debe ser 00000000-0.');
            if (!/^\d{4}-\d{4}$/.test(tel)) errores.push('El teléfono debe ser 0000-0000.');
            if (!correo.includes('@')) errores.push('El correo debe incluir "@".');

            if (errores.length > 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Corrija los siguientes errores:',
                    html: errores.join('<br>'),
                    confirmButtonColor: '#d33'
                });
            }
        });
    });

    // =====================================================
    // ELIMINAR CON SWEETALERT
    // =====================================================
    document.addEventListener("click", function(e) {
        if (e.target.closest(".btn-delete")) {

            let id = e.target.closest(".btn-delete").dataset.id;

            Swal.fire({
                title: "¿Eliminar solicitante?",
                text: "Esta acción es irreversible.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById("deleteForm-" + id).submit();
                }
            });
        }
    });

});
</script>
@endpush
