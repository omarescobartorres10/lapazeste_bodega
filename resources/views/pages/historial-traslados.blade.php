@extends('welcome')

@section('title', 'Historial de Traslados')

@section('content')

<style>
    .historial-traslados-page .btn-circle-solid {
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
    .historial-traslados-page .btn-blue   { background-color: #0d6efd !important; }
    .historial-traslados-page .btn-yellow { background-color: #ffc107 !important; color:#000 !important; }

    .historial-traslados-page .btn-white-outline {
        background: white !important;
        border: 2px solid #ffffff !important;
    }
    .historial-traslados-page .btn-white-outline i { color: #0d6efd !important; }
    .historial-traslados-page .pagination { gap: .25rem; }
    .historial-traslados-page .page-link {
        min-width: 36px !important;
        min-height: 36px !important;
        font-size: 0.9rem !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0.375rem 0.75rem !important;
    }
    .historial-traslados-page .page-link i,
    .historial-traslados-page .page-link span {
        font-size: 0.9rem !important;
    }
</style>

<div class="container-fluid py-4 historial-traslados-page">
    <h1 class="mb-4">🚚 Historial de Traslados</h1>

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
        <div class="card-header text-white fw-bold" style="background-color:#0d6efd;">
            <i class="bi bi-truck"></i> Traslados agrupados por operación
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Código</th>
                        <th>Bodega Origen</th>
                        <th>Bodega Destino</th>
                        <th>Ítems</th>
                        <th>Total</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($traslados as $t)
                        <tr class="{{ (int)$t->estado === 0 ? 'table-danger' : '' }}">
                            <td><strong>{{ $t->codigo_operacion }}</strong></td>

                            <td>{{ strtoupper($bodegas[$t->bodega_origen_id] ?? '---') }}</td>
                            <td>{{ strtoupper($bodegas[$t->bodega_destino_id] ?? '---') }}</td>

                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $t->total_items }}</span>
                            </td>

                            <td class="text-center">
                                <span class="badge bg-primary">{{ $t->total_cantidad }}</span>
                            </td>

                            <td>{{ \Carbon\Carbon::parse($t->fecha)->format('d/m/Y H:i') }}</td>

                            <td>
                                @if((int)$t->estado === 1)
                                    <span class="badge bg-success">ACTIVO</span>
                                @else
                                    <span class="badge bg-danger">CANCELADO</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">

                                    {{-- Detalle --}}
                                    <button type="button"
                                            class="btn-circle-solid btn-yellow btn-ver-detalle"
                                            data-codigo="{{ $t->codigo_operacion }}"
                                            title="Ver detalle">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    {{-- PDF (igual que salidas) --}}
                                    <a href="{{ route('traslados.pdf', $t->codigo_operacion) }}"
                                       class="btn-circle-solid btn-blue"
                                       target="_blank"
                                       title="Descargar PDF">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-3 text-muted">No hay registros.</td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $traslados->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

{{-- MODAL DETALLE --}}
<div class="historial-traslados-page">
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detalle del traslado</h5>

                {{-- ✅ Botón PDF del modal (SIN $t) --}}
                <a id="btn-pdf-detalle"
                   href="#"
                   class="btn-circle-solid btn-white-outline ms-2"
                   target="_blank">
                    <i class="bi bi-file-earmark-pdf"></i>
                </a>

                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="detalle-error" class="alert alert-danger d-none"></div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Producto Origen</th>
                                <th>Producto Destino</th>
                                <th>Bodega Origen</th>
                                <th>Bodega Destino</th>
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
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.btn-ver-detalle').forEach(btn => {
        btn.addEventListener('click', async () => {

            const codigo = btn.dataset.codigo;

            // ✅ Link PDF del modal (igual que salidas)
            document.getElementById('btn-pdf-detalle')
                .setAttribute('href', `/traslados/pdf/${codigo}`);

            // reset
            const errBox = document.getElementById('detalle-error');
            errBox.classList.add('d-none');
            errBox.innerHTML = '';

            const tbody = document.getElementById('tabla-detalle');
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-3">Cargando...</td></tr>`;

            try {
                const res = await fetch(`/traslados/detalle/${codigo}`);
                if (!res.ok) throw new Error('No se pudo obtener el detalle.');

                const data = await res.json();

                let html = '';
                if (!data || data.length === 0) {
                    html = `<tr><td colspan="6" class="text-center text-muted py-3">Sin detalle.</td></tr>`;
                } else {
                    data.forEach(e => {
                        html += `
                            <tr>
                                <td>${e.producto_origen ?? '---'}</td>
                                <td>${e.producto_destino ?? '---'}</td>
                                <td>${e.bodega_origen ?? '---'}</td>
                                <td>${e.bodega_destino ?? '---'}</td>
                                <td>${e.cantidad ?? 0}</td>
                                <td>${e.observacion ?? ''}</td>
                            </tr>
                        `;
                    });
                }

                tbody.innerHTML = html;

            } catch (err) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-3">Error cargando detalle.</td></tr>`;
                errBox.classList.remove('d-none');
                errBox.innerText = err.message || 'Error desconocido';
            }

            new bootstrap.Modal(document.getElementById('modalDetalle')).show();
        });
    });

});
</script>

@endsection