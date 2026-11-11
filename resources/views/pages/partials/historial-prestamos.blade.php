<div class="card shadow rounded-4 border-0">
    <div class="card-header fw-bold text-white d-flex align-items-center" style="background-color: #6f42c1;">
        <i class="bi bi-clock-history me-2"></i> Historial de préstamos
    </div>

    <div class="card-body table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Bodega</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th class="text-center">Cantidad</th>
                    <th>Destino</th>
                    <th>Recibido por</th>
                    <th>Fecha préstamo</th>
                    <th>Fecha devolución</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prestamos as $p)
                    <tr>
                        <td>{{ strtoupper($p->bodega->nombre ?? $p->producto->bodega->nombre ?? '—') }}</td>
                        <td>{{ strtoupper($p->producto->nombre) }}</td>
                        <td>{{ strtoupper($p->categoria->nombre) }}</td>
                        <td class="text-center">{{ $p->cantidad }}</td>
                        <td>{{ $p->destino }}</td>
                        <td>{{ $p->recibido_por }}</td>
                        <td>{{ $p->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $p->fecha_devolucion ? $p->fecha_devolucion->format('d/m/Y H:i') : '—' }}</td>
                        <td class="text-center">
                            @switch($p->estado)
                                @case('prestado')
                                    <span class="badge bg-warning text-dark px-3 py-2">Prestado</span>
                                    @break
                                @case('devuelto')
                                    <span class="badge bg-success px-3 py-2">Devuelto</span>
                                    @break
                                @case('cancelado')
                                    <span class="badge bg-secondary px-3 py-2">Cancelado</span>
                                    @break
                                @default
                                    <span class="badge bg-light text-dark px-3 py-2">Desconocido</span>
                            @endswitch
                        </td>
                        <td class="text-center">
                            @if($p->estado === 'prestado')
                                <div class="d-flex justify-content-center gap-2">
                                    <form action="{{ route('prestamos.devolver', $p->id) }}" method="POST" class="m-0">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-check2-circle"></i> Devolver
                                        </button>
                                    </form>
                                    <form action="{{ route('prestamos.cancelar', $p->id) }}" method="POST" class="m-0">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-x-circle"></i> Cancelar
                                        </button>
                                    </form>
                                </div>
                            @else
                                <em class="text-muted">Sin acciones</em>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            No hay préstamos registrados todavía.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
