@extends('welcome')

@section('title', 'Historial de Salidas')

@section('content')

<style>
    .historial-salidas-page .btn-circle-solid {
        width: 38px;
        height: 38px;
        border-radius: 50% !important;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        border: none !important;
        color: white !important;
        font-size: 18px;
        cursor: pointer;
    }
    .historial-salidas-page .btn-blue   { background-color: #007bff !important; }
    .historial-salidas-page .btn-yellow { background-color: #ffc107 !important; color: #000 !important; }
    .historial-salidas-page .btn-red    { background-color: #dc3545 !important; }
    .historial-salidas-page .btn-circle-solid i { font-size: 17px; }
    .historial-salidas-page .btn-white-outline {
        background: white !important;
        border: 2px solid #ffffff !important;
    }
    .historial-salidas-page .btn-white-outline i {
        color: #007bff !important;
    }
    .historial-salidas-page .pagination { gap: .25rem; }
    .historial-salidas-page .page-link {
        min-width: 36px !important;
        min-height: 36px !important;
        font-size: 0.9rem !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0.375rem 0.75rem !important;
    }
    .historial-salidas-page .page-link i,
    .historial-salidas-page .page-link span {
        font-size: 0.9rem !important;
    }
</style>

<div class="container-fluid py-4 historial-salidas-page">

    <h1 class="mb-4">📑 Historial de Salidas</h1>

    {{-- Info de bodega / admin --}}
    @if($bodega)
        <div class="alert alert-info d-flex align-items-center mb-3 shadow-sm rounded-pill px-3 py-2">
            <i class="bi bi-shop me-2 fs-5"></i>
            <div>
                <strong>Bodega asignada:</strong> {{ strtoupper($bodega->nombre) }} <br>
                <small>Solo se muestran salidas de esta bodega.</small>
            </div>
        </div>
    @else
        <div class="alert alert-warning d-flex align-items-center mb-3 shadow-sm rounded-pill px-3 py-2">
            <i class="bi bi-shield-lock me-2 fs-5"></i>
            <div>
                <strong>Modo administrador:</strong> Se muestran salidas de todas las bodegas.
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
                    <button class="btn btn-success w-100">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- Tabla agrupada --}}
    <div class="card shadow">
        <div class="card-header text-white fw-bold" style="background-color:#dc3545;">
            <i class="bi bi-boxes"></i> Salidas agrupadas por operación
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Código operación</th>
                        <th>Bodega</th>
                        <th>Departamento</th>
                        <th>Destino</th>
                        <th>Total productos</th>
                        <th>Fecha</th>
                        <th>Entregado por</th>
                        <th>Recibido por</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($salidas as $sal)
                        <tr class="{{ $sal->estado == 0 ? 'table-danger' : '' }}">
                            <td><strong>{{ $sal->codigo_operacion }}</strong></td>

                            <td>{{ strtoupper($bodegas[$sal->bodega_id] ?? '---') }}</td>
                            <td>{{ strtoupper($departamentos[$sal->departamento_id] ?? '---') }}</td>
                            <td>{{ strtoupper($sal->destino) }}</td>

                            <td class="text-center">
                                <span class="badge bg-primary">{{ $sal->total_productos }}</span>
                            </td>

                            <td>{{ \Carbon\Carbon::parse($sal->fecha)->format('d/m/Y H:i') }}</td>

                            <td>{{ strtoupper($sal->entregado_por) }}</td>
                            <td>{{ strtoupper($sal->recibido_por) }}</td>

                            <td>
                                @if($sal->estado == 1)
                                    <span class="badge bg-success">CREADA</span>
                                @else
                                    <span class="badge bg-danger">CANCELADA</span>
                                @endif
                            </td>

                            {{-- ACCIONES --}}
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">

                                    {{-- Ver detalle --}}
                                    <button class="btn-circle-solid btn-yellow ver-detalle"
                                            data-codigo="{{ $sal->codigo_operacion }}"
                                            title="Ver detalle">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    {{-- PDF --}}
                                    <a href="{{ route('salidas.pdf', $sal->codigo_operacion) }}"
                                        class="btn-circle-solid btn-blue"
                                        target="_blank"
                                        title="Descargar PDF">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>

                                    {{-- Cancelar SOLO si está activa --}}
                                    @if($sal->estado == 1)
                                    <button class="btn-circle-solid btn-red cancelar-salida"
                                            data-codigo="{{ $sal->codigo_operacion }}"
                                            title="Cancelar salida">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif

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

        <div class="d-flex justify-content-center mt-3">
            {{ $salidas->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>

</div>

{{-- MODAL DETALLE --}}
<div class="historial-salidas-page">
<div class="modal fade" id="modalDetalleSalida" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Detalle de salida</h5>

                <a id="btn-pdf-detalle-salida"
                    href="#"
                    class="btn-circle-solid btn-white-outline ms-2"
                    target="_blank">
                    <i class="bi bi-file-earmark-pdf"></i>
                </a>

                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Dpto</th>
                                <th>Cantidad</th>
                                <th>Obs</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-detalle-salida"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
/* VER DETALLE */
document.querySelectorAll('.ver-detalle').forEach(btn => {
    btn.addEventListener('click', function () {
        let codigo = this.dataset.codigo;

        document.getElementById('btn-pdf-detalle-salida')
            .setAttribute('href', `/salidas/pdf/${codigo}`);

        fetch(`/salidas/detalle/${codigo}`)
            .then(res => res.json())
            .then(data => {
                let html = '';
                data.forEach(e => {
                    html += `
                        <tr>
                            <td>${e.producto.nombre}</td>
                            <td>${e.categoria.nombre}</td>
                            <td>${e.departamento.nombre}</td>
                            <td>${e.cantidad}</td>
                            <td>${e.observacion ?? ''}</td>
                        </tr>`;
                });

                document.getElementById('tabla-detalle-salida').innerHTML = html;

                new bootstrap.Modal(document.getElementById('modalDetalleSalida')).show();
            });
    });
});

/* CANCELAR SALIDA */
document.querySelectorAll('.cancelar-salida').forEach(btn => {
    btn.addEventListener('click', function () {
        let codigo = this.dataset.codigo;

        Swal.fire({
            title: "¿Cancelar salida?",
            html: `
                <b>${codigo}</b><br><br>
                La salida será marcada como <span style="color:red;">CANCELADA</span>.<br>
                El stock será restituido.<br><br>
                ¿Deseas continuar?
            `,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sí, cancelar",
            cancelButtonText: "No"
        }).then((result) => {
            if (result.isConfirmed) {

                fetch(`/salidas/${codigo}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: "Salida cancelada",
                            text: "Se restituyó el stock y se guardó el historial.",
                            icon: "success",
                            timer: 1500,
                            showConfirmButton: false
                        });

                        setTimeout(() => location.reload(), 1600);
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: data.message,
                            icon: "error"
                        });
                    }
                });
            }
        });
    });
});
</script>

@endsection
