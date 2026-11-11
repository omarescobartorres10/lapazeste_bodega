@extends('welcome')

@section('title', 'Historial de Entradas')

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
            color: white !important;
            font-size: 18px;
            cursor: pointer;
            text-decoration: none !important;
        }

        .btn-blue { background-color: #007bff !important; }
        .btn-yellow { background-color: #ffc107 !important; color: #000 !important; }
        .btn-red { background-color: #dc3545 !important; }
        .btn-green { background-color: #198754 !important; }

        .btn-circle-solid i { font-size: 17px; }

        .btn-white-outline {
            background: white !important;
            border: 2px solid #ffffff !important;
        }

        .btn-white-outline i { color: #007bff !important; }
    </style>
</head>

<div class="container-fluid py-4">

    <h1 class="mb-4">📑 Historial de Entradas</h1>

    @php
        $bodegaUser = auth()->user()->bodega_id;
    @endphp

    @if($bodegaUser)
        <div class="alert alert-info d-flex align-items-center mb-4 shadow-sm rounded-pill px-3 py-2">
            <i class="bi bi-shop me-2 fs-5"></i>
            <div>
                <strong>Bodega asignada:</strong>
                {{ strtoupper($bodegas[$bodegaUser]) }} <br>
                <small>Solo se muestran entradas de esta bodega.</small>
            </div>
        </div>
    @else
        <div class="alert alert-warning d-flex align-items-center mb-4 shadow-sm rounded-pill px-3 py-2">
            <i class="bi bi-shield-lock me-2 fs-5"></i>
            <div>
                <strong>Modo administrador:</strong>
                Usted puede ver el historial de TODAS las bodegas.
            </div>
        </div>
    @endif

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

    <div class="card shadow">
        <div class="card-header bg-success text-white fw-bold">
            <i class="bi bi-boxes"></i> Entradas agrupadas por operación
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Código operación</th>
                        <th>Bodega</th>
                        <th>Departamento</th>
                        <th>Total productos</th>
                        <th>Fecha</th>
                        <th>Entregado por</th>
                        <th>Recibido por</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($entradas as $ent)
                        <tr class="{{ $ent->estado == 0 ? 'table-danger' : '' }}">

                            <td><strong>{{ $ent->codigo_operacion }}</strong></td>

                            <td>{{ strtoupper($bodegas[$ent->bodega_id] ?? '---') }}</td>
                            <td>{{ strtoupper($departamentos[$ent->departamento_id] ?? '---') }}</td>

                            <td class="text-center">
                                <span class="badge bg-primary">{{ $ent->total_productos }}</span>
                            </td>

                            <td>{{ \Carbon\Carbon::parse($ent->fecha)->format('d/m/Y H:i') }}</td>

                            <td>{{ strtoupper($ent->entregado_por) }}</td>
                            <td>{{ strtoupper($ent->recibido_por) }}</td>

                            <td>
                                @if($ent->estado == 1)
                                    <span class="badge bg-success">CREADA</span>
                                @else
                                    <span class="badge bg-danger">CANCELADA</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">

                                    {{-- Ver detalle --}}
                                    <button 
                                        class="btn-circle-solid btn-yellow ver-detalle"
                                        data-codigo="{{ $ent->codigo_operacion }}"
                                        title="Ver detalle">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    {{-- PDF de productos --}}
                                    <a 
                                        href="{{ route('entradas.pdf', $ent->codigo_operacion) }}"
                                        class="btn-circle-solid btn-blue"
                                        target="_blank"
                                        title="PDF de entrada">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>

                                    {{-- Factura PDF --}}
                                    @if($ent->factura_pdf)
                                        <a 
                                            href="{{ route('facturas.ver', basename($ent->factura_pdf)) }}"
                                            class="btn-circle-solid btn-green"
                                            target="_blank"
                                            title="Ver factura">
                                            <i class="bi bi-receipt"></i>
                                        </a>
                                    @else
                                        <span class="text-muted small align-self-center">Sin factura</span>
                                    @endif

                                    {{-- Eliminar --}}
                                    @if($ent->estado == 1)
                                        <button 
                                            class="btn-circle-solid btn-red eliminar-entrada"
                                            data-codigo="{{ $ent->codigo_operacion }}"
                                            title="Cancelar entrada">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-3 text-muted">No hay registros.</td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <div class="p-3">
            {{ $entradas->links() }}
        </div>
    </div>

</div>

<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detalle de entrada</h5>

                <a 
                    id="btn-pdf-detalle"
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
                            <th>Dpto</th>
                            <th>Cantidad</th>
                            <th>Obs</th>
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
document.querySelectorAll('.ver-detalle').forEach(btn => {
    btn.addEventListener('click', function() {
        let codigo = this.dataset.codigo;

        document.getElementById("btn-pdf-detalle")
            .setAttribute("href", `/entradas/pdf/${codigo}`);

        fetch(`/entradas/historial/detalle/${codigo}`)
            .then(res => res.json())
            .then(data => {
                let tbody = document.getElementById('tabla-detalle');
                tbody.innerHTML = '';

                if (data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                No hay detalle para esta entrada.
                            </td>
                        </tr>
                    `;
                    return;
                }

                data.forEach(item => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${item.producto ? item.producto.nombre : '---'}</td>
                            <td>${item.categoria ? item.categoria.nombre : '---'}</td>
                            <td>${item.departamento ? item.departamento.nombre : '---'}</td>
                            <td>${item.cantidad}</td>
                            <td>${item.observacion ? item.observacion : '---'}</td>
                        </tr>
                    `;
                });

                let modal = new bootstrap.Modal(document.getElementById('modalDetalle'));
                modal.show();
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar el detalle de la entrada.'
                });
            });
    });
});

document.querySelectorAll('.eliminar-entrada').forEach(btn => {
    btn.addEventListener('click', function() {
        let codigo = this.dataset.codigo;

        Swal.fire({
            title: "¿Cancelar entrada?",
            text: "Se descontará el stock ingresado en esta entrada.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Sí, cancelar",
            cancelButtonText: "No"
        }).then(result => {
            if (result.isConfirmed) {
                fetch(`/entradas/${codigo}`, {
                    method: "DELETE",
                    headers: { 
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json"
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: "Entrada cancelada",
                            text: "Se revirtió el stock y se guardó el historial.",
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
                })
                .catch(() => {
                    Swal.fire({
                        title: "Error",
                        text: "No se pudo cancelar la entrada.",
                        icon: "error"
                    });
                });
            }
        });
    });
});
</script>
@endsection