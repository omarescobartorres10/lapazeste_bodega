<div class="card shadow border-0 rounded-4 mt-4">
    <div class="card-header bg-dark text-white fw-bold">
        <i class="bi bi-archive me-2"></i>Historial de Bajas
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>#</th>
                        <th>Bodega</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Cantidad</th>
                        <th>Motivo</th>
                        <th>Responsable</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bajas as $baja)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $baja->bodega->nombre ?? 'N/A' }}</td>
                            <td>{{ $baja->producto->nombre ?? 'N/A' }}</td>
                            <td>{{ $baja->categoria->nombre ?? 'N/A' }}</td>
                            <td class="text-center">{{ $baja->cantidad }}</td>
                            <td>{{ $baja->motivo }}</td>
                            <td>{{ $baja->responsable }}</td>
                            <td class="text-center">
                                @if($baja->estado)
                                    <span class="badge bg-success">Procesado</span>
                                @else
                                    <span class="badge bg-danger">Cancelado</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($baja->estado)
                                    <form action="{{ route('bajas.cancelar', $baja->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-x-circle"></i> Cancelar
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-outline-secondary btn-sm" disabled>
                                        <i class="bi bi-lock"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted">No hay registros de bajas</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end">
            {{ $bajas->links() }}
        </div>
    </div>
</div>
