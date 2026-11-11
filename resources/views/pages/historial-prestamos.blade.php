@extends('welcome')

@section('title', 'Historial de Préstamos')

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
            font-size: 18px;
            cursor: pointer;
            color: #fff !important;
        }
        .btn-purple { background-color: #6f42c1 !important; }
        .btn-blue   { background-color: #0d6efd !important; }
        .btn-yellow { background-color: #ffc107 !important; color:#000 !important; }
        .btn-green  { background-color: #198754 !important; }
        .btn-red    { background-color: #dc3545 !important; }

        .btn-white-outline {
            background: white !important;
            border: 2px solid #ffffff !important;
        }
        .btn-white-outline i { color: #6f42c1 !important; }
    </style>
</head>

<div class="container-fluid py-4">
    <h1 class="mb-4">📚 Historial de Préstamos</h1>

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

    {{-- Tabla --}}
    <div class="card shadow">
        <div class="card-header text-white fw-bold" style="background-color:#6f42c1;">
            <i class="bi bi-arrow-left-right"></i> Préstamos agrupados por operación
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Código</th>
                        <th>Bodega</th>
                        <th>Departamento</th>
                        <th>Ítems</th>
                        <th>Prestado</th>
                        <th>Devuelto</th>
                        <th>Pendiente</th>
                        <th>Fecha</th>
                        <th>Destino</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($prestamos as $pre)
                        @php
                            $pendiente = $pre->total_prestado - $pre->total_devuelto;
                        @endphp

                        <tr class="
                            {{ $pre->estado === 'cancelado' ? 'table-danger' : '' }}
                            {{ $pre->estado === 'devuelto'  ? 'table-success' : '' }}
                        ">
                            <td><strong>{{ $pre->codigo_operacion }}</strong></td>

                            <td>{{ strtoupper($bodegas[$pre->bodega_id] ?? '---') }}</td>
                            <td>{{ strtoupper($departamentos[$pre->departamento_id] ?? '---') }}</td>

                            <td class="text-center"><span class="badge bg-secondary">{{ $pre->total_items }}</span></td>
                            <td class="text-center"><span class="badge bg-primary">{{ $pre->total_prestado }}</span></td>
                            <td class="text-center"><span class="badge bg-success">{{ $pre->total_devuelto }}</span></td>
                            <td class="text-center"><span class="badge bg-warning text-dark">{{ $pendiente }}</span></td>

                            <td>{{ \Carbon\Carbon::parse($pre->fecha)->format('d/m/Y H:i') }}</td>
                            <td>{{ strtoupper($pre->destino) }}</td>

                            <td>
                                @if($pre->estado === 'prestado')
                                    <span class="badge bg-warning text-dark">PRESTADO</span>
                                @elseif($pre->estado === 'parcial')
                                    <span class="badge bg-info text-dark">PARCIAL</span>
                                @elseif($pre->estado === 'devuelto')
                                    <span class="badge bg-success">DEVUELTO</span>
                                @elseif($pre->estado === 'cancelado')
                                    <span class="badge bg-danger">CANCELADO</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">

                                    {{-- Detalle --}}
                                    <button class="btn-circle-solid btn-yellow btn-ver-detalle"
                                        data-codigo="{{ $pre->codigo_operacion }}">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    {{-- PDF --}}
                                    <a href="{{ route('prestamos.pdf', $pre->codigo_operacion) }}"
                                        class="btn-circle-solid btn-blue"
                                        target="_blank">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>

                                    {{-- Devolver todo --}}
                                    @if(in_array($pre->estado, ['prestado','parcial']))
                                        <button class="btn-circle-solid btn-green btn-devolver-todo"
                                            data-codigo="{{ $pre->codigo_operacion }}">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>

                                        {{-- Devolución parcial --}}
                                        <button class="btn-circle-solid btn-purple btn-devolver-parcial"
                                            data-codigo="{{ $pre->codigo_operacion }}">
                                            <i class="bi bi-scissors"></i>
                                        </button>
                                    @endif

                                    {{-- Cancelar --}}
                                    @if(!in_array($pre->estado, ['cancelado','devuelto']))
                                        <button class="btn-circle-solid btn-red btn-cancelar-op"
                                            data-codigo="{{ $pre->codigo_operacion }}">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-3 text-muted">No hay registros.</td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <div class="p-3">
            {{ $prestamos->links() }}
        </div>
    </div>
</div>

{{-- ===================== --}}
{{-- MODAL DETALLE --}}
{{-- ===================== --}}
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detalle del préstamo</h5>

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
                            <th>Dpto</th>
                            <th>Prestado</th>
                            <th>Devuelto</th>
                            <th>Pendiente</th>
                            <th>Obs</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-detalle"></tbody>
                </table>
            </div>

        </div>
    </div>
</div>

{{-- ===================== --}}
{{-- MODAL DEVOLUCIÓN PARCIAL --}}
{{-- ===================== --}}
<div class="modal fade" id="modalParcial" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Devolución parcial de préstamo</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="form-devolucion-parcial" method="POST">
                @csrf
                <div class="modal-body">

                    <p class="text-muted mb-2">
                        Ingrese la cantidad a devolver por cada producto (no puede superar lo pendiente).
                    </p>

                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Prestado</th>
                                <th>Devuelto</th>
                                <th>Pendiente</th>
                                <th>Devolver ahora</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-parcial"></tbody>
                    </table>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-success" type="submit">
                        <i class="bi bi-check-circle me-1"></i> Confirmar devolución parcial
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ===============================
       VER DETALLE
    =============================== */
    document.querySelectorAll('.btn-ver-detalle').forEach(btn => {
        btn.addEventListener('click', () => {
            const codigo = btn.dataset.codigo;

            document.getElementById('btn-pdf-detalle')
                .setAttribute('href', `/prestamos/pdf/${codigo}`);

            fetch(`/prestamos/historial/detalle/${codigo}`)
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
                                <td>${e.cantidad_devuelta}</td>
                                <td>${e.pendiente}</td>
                                <td>${e.observacion ?? ''}</td>
                            </tr>
                        `;
                    });

                    document.getElementById('tabla-detalle').innerHTML = html;

                    new bootstrap.Modal(document.getElementById('modalDetalle')).show();
                });
        });
    });


    /* ===============================
       DEVOLVER TODO
    =============================== */
    document.querySelectorAll('.btn-devolver-todo').forEach(btn => {

        btn.addEventListener('click', () => {

            const codigo = btn.dataset.codigo;

            Swal.fire({
                title: "¿Devolver todos los productos?",
                html: `<b>${codigo}</b><br>
                       Se registrará la devolución completa de todo lo pendiente.`,
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#198754",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Sí, devolver todo"
            }).then(result => {

                if (!result.isConfirmed) return;

                fetch(`/prestamos/devolver-todo/${codigo}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                })
                .then(res => res.json())
                .then(data => {

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Devolución registrada',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        setTimeout(() => location.reload(), 1700);

                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }

                })
            })

        });

    });


    /* ===============================
       DEVOLUCIÓN PARCIAL
    =============================== */
    let codigoActual = null;

    document.querySelectorAll('.btn-devolver-parcial').forEach(btn => {

        btn.addEventListener('click', () => {

            codigoActual = btn.dataset.codigo;

            fetch(`/prestamos/historial/detalle/${codigoActual}`)
                .then(res => res.json())
                .then(data => {

                    let html = '';
                    let index = 0;

                    data.forEach(item => {

                        html += `
                            <tr>
                                <td>${item.producto}</td>
                                <td>${item.cantidad}</td>
                                <td>${item.cantidad_devuelta}</td>
                                <td>${item.pendiente}</td>

                                <td>
                                    <input type="hidden" name="items[${index}][id]" value="${item.id}">

                                    <input type="number"
                                        class="form-control form-control-sm"
                                        name="items[${index}][devolver]"
                                        min="0"
                                        max="${item.pendiente}"
                                        value="0"
                                        ${item.pendiente == 0 ? 'disabled' : ''}>
                                </td>
                            </tr>
                        `;

                        index++;
                    });

                    document.getElementById('tabla-parcial').innerHTML = html;

                    const formParcial = document.getElementById('form-devolucion-parcial');
                    formParcial.setAttribute('action', `/prestamos/devolver-parcial/${codigoActual}`);

                    new bootstrap.Modal(document.getElementById('modalParcial')).show();
                });
        });

    });


    document.getElementById('form-devolucion-parcial')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const formData = new FormData(form);

        fetch(form.getAttribute('action'), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async res => {
            let data = await res.json();

            if (data.success) {

                Swal.fire({
                    icon: 'success',
                    title: 'Devolución parcial registrada',
                    timer: 1500,
                    showConfirmButton: false
                });

                setTimeout(() => location.reload(), 1600);

            } else {
                Swal.fire('Error', data.message || 'Datos inválidos', 'error');
            }
        })
        .catch(() => {
            Swal.fire('Error', 'Fallo la comunicación con el servidor.', 'error');
        });

    });


    /* ===============================
       CANCELAR PRÉSTAMO
    =============================== */
    document.querySelectorAll('.btn-cancelar-op').forEach(btn => {

        btn.addEventListener('click', () => {
            const codigo = btn.dataset.codigo;

            Swal.fire({
                title: "¿Cancelar préstamo?",
                html: `<b>${codigo}</b><br>
                       Se restaurará el stock pendiente.`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#dc3545",
                confirmButtonText: "Sí, cancelar"
            }).then(result => {

                if (!result.isConfirmed) return;

                fetch(`/prestamos/${codigo}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Préstamo cancelado',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        setTimeout(() => location.reload(), 1600);

                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }

                })
                .catch(() => {
                    Swal.fire('Error', 'Error de comunicación con el servidor.', 'error');
                });

            });

        });

    });


});
</script>

@endsection
