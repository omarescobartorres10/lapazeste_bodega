@extends('welcome')

@section('title', 'Historial de Bajas')

@section('content')

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">

    <style>
        .btn-circle-solid {
            width: 38px;
            height: 38px;
            border-radius: 50% !important;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            border: none !important;
            color: #fff !important;
            font-size: 18px;
            cursor: pointer;
        }

        .btn-red    { background-color: #dc3545 !important; }
        .btn-yellow { background-color: #ffc107 !important; color:#000 !important; }
        .btn-blue   { background-color: #0d6efd !important; }

        .btn-circle-solid i { font-size: 17px; }

        .btn-white-outline {
            background: white !important;
            border: 2px solid #ffffff !important;
        }
        .btn-white-outline i {
            color: #dc3545 !important;
        }
    </style>
</head>

<div class="container-fluid py-4">

    <h1 class="mb-4 text-danger">
        🔥 Historial de Bajas
    </h1>

    {{-- Info bodega / admin --}}
    @if($bodega)
        <div class="alert alert-info d-flex align-items-center mb-3 shadow-sm rounded-pill px-3 py-2">
            <i class="bi bi-shop me-2 fs-5"></i>
            <div>
                <strong>Bodega asignada:</strong> {{ strtoupper($bodega->nombre) }} <br>
                <small>Solo se muestran bajas de esta bodega.</small>
            </div>
        </div>
    @else
        <div class="alert alert-warning d-flex align-items-center mb-3 shadow-sm rounded-pill px-3 py-2">
            <i class="bi bi-shield-lock me-2 fs-5"></i>
            <div>
                <strong>Modo administrador:</strong> Se muestran todas las bodegas.
            </div>
        </div>
    @endif

    {{-- Filtro --}}
    <div class="card shadow mb-3">
        <div class="card-body">
            <form class="row g-3" method="GET">

                <div class="col-md-4">
                    <label class="fw-semibold">Fecha desde:</label>
                    <input type="date" name="desde" class="form-control" value="{{ $desde }}">
                </div>

                <div class="col-md-4">
                    <label class="fw-semibold">Fecha hasta:</label>
                    <input type="date" name="hasta" class="form-control" value="{{ $hasta }}">
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-danger w-100">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="card shadow">
        <div class="card-header text-white fw-bold" style="background-color:#dc3545;">
            <i class="bi bi-fire"></i> Bajas agrupadas por operación
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Código operación</th>
                        <th>Bodega</th>
                        <th>Departamento</th>
                        <th>Ítems</th>
                        <th>Fecha</th>
                        <th>Motivo</th>
                        <th>Responsable</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($bajas as $baj)
                        <tr class="{{ $baj->estado == 0 ? 'table-danger' : '' }}">

                            <td><strong>{{ $baj->codigo_operacion }}</strong></td>

                            <td>{{ strtoupper($bodegas[$baj->bodega_id] ?? '---') }}</td>
                            <td>{{ strtoupper($departamentos[$baj->departamento_id] ?? '---') }}</td>

                            <td class="text-center">
                                <span class="badge bg-danger">{{ $baj->total_items }}</span>
                            </td>

                            <td>{{ \Carbon\Carbon::parse($baj->fecha)->format('d/m/Y H:i') }}</td>

                            <td>{{ strtoupper($baj->motivo) }}</td>
                            <td>{{ strtoupper($baj->responsable) }}</td>

                            <td>
                                @if($baj->estado == 1)
                                    <span class="badge bg-success">CREADA</span>
                                @else
                                    <span class="badge bg-danger">CANCELADA</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">

                                    {{-- 🟡 Ver detalle --}}
                                    <button class="btn-circle-solid btn-yellow ver-detalle"
                                        data-codigo="{{ $baj->codigo_operacion }}">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    {{-- 🔵 PDF --}}
                                    <a href="{{ route('bajas.pdf', $baj->codigo_operacion) }}"
                                        class="btn-circle-solid btn-blue"
                                        target="_blank">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>

                                    {{-- 🔴 Cancelar operación --}}                                    
                                    @auth
                                        @if(!Auth::user()->bodega && $baj->estado == 1)
                                            <button class="btn-circle-solid btn-red cancelar-baja"
                                                data-codigo="{{ $baj->codigo_operacion }}">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        @endif
                                    @endauth

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-3 text-muted">No hay registros.</td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <div class="p-3">
            {{ $bajas->links() }}
        </div>
    </div>

</div>


{{-- MODAL DETALLE --}}
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Detalle de baja</h5>

                <a id="btn-pdf-detalle"
                    href="#"
                    class="btn-circle-solid btn-white-outline ms-2"
                    target="_blank">
                    <i class="bi bi-file-earmark-pdf"></i>
                </a>

                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Departamento</th>
                            <th>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-detalle"></tbody>
                </table>
            </div>

        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    /* =========================
       🔍 VER DETALLE
       ========================= */
    document.querySelectorAll('.ver-detalle').forEach(btn => {
        btn.addEventListener('click', () => {

            const codigo = btn.dataset.codigo;

            document.getElementById('btn-pdf-detalle')
                .setAttribute('href', `/bajas/pdf/${codigo}`);

            fetch(`/bajas/historial/detalle/${codigo}`)
                .then(res => res.json())
                .then(data => {
                    let html = '';

                    data.forEach(e => {
                        html += `
                            <tr>
                                <td>${e.producto}</td>
                                <td>${e.categoria}</td>
                                <td>${e.departamento}</td>
                                <td>${e.cantidad}</td>
                            </tr>`;
                    });

                    document.getElementById('tabla-detalle').innerHTML = html;

                    new bootstrap.Modal(document.getElementById('modalDetalle')).show();
                });
        });
    });


    /* =========================
       ❌ CANCELAR BAJA
       ========================= */
    document.querySelectorAll('.cancelar-baja').forEach(btn => {

        btn.addEventListener('click', () => {

            const codigo = btn.dataset.codigo;

            Swal.fire({
                title: "¿Cancelar baja?",
                html: `
                    <b>${codigo}</b><br><br>
                    Los productos serán devueltos al stock.<br>
                    La operación será marcada como <b style="color:red;">CANCELADA</b>.
                `,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Sí, cancelar",
                cancelButtonText: "No"
            }).then(result => {
                if (!result.isConfirmed) return;

                fetch(`/bajas/${codigo}`, {
                    method: "DELETE",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {

                    if (data.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Baja cancelada",
                            text: data.message,
                            timer: 1600,
                            showConfirmButton: false
                        });
                        setTimeout(() => location.reload(), 1700);
                    } else {
                        Swal.fire("Error", data.message, "error");
                    }

                });
            });
        });

    });

});
</script>

@endsection
