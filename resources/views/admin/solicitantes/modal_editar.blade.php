<div class="modal fade" id="modalEditar{{ $s->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow rounded-4 border-0">
            <div class="modal-header text-white rounded-top-4" style="background-color: #198754;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-pencil-square me-2"></i> Editar Solicitante
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form class="form-solicitante" method="POST"
                    action="{{ route('solicitantes.update', $s->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">DUI</label>
                            <input type="text" name="dui"
                                class="form-control input-dui"
                                value="{{ $s->dui }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Código de marcador</label>
                            <input type="text" name="codigo_marcador"
                                class="form-control input-marcador"
                                value="{{ $s->codigo_marcador }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Estado</label>
                            <select name="estado" class="form-select" required>
                                <option value="activo" {{ $s->estado == 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="inactivo" {{ $s->estado == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre completo</label>
                            <input type="text" name="nombre" class="form-control"
                                value="{{ $s->nombre }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cargo</label>
                            <input type="text" name="cargo" class="form-control"
                                value="{{ $s->cargo }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Departamento</label>
                            <select name="departamento_id" class="form-select" required>
                                <option value="">Seleccione...</option>
                                @foreach($departamentos as $d)
                                    <option value="{{ $d->id }}"
                                        {{ $s->departamento_id == $d->id ? 'selected' : '' }}>
                                        {{ strtoupper($d->nombre) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="text" name="telefono"
                                class="form-control input-telefono"
                                value="{{ $s->telefono }}" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Correo</label>
                            <input type="email" name="correo"
                                class="form-control"
                                value="{{ $s->correo }}" required>
                        </div>

                    </div>

                    <div class="text-end mt-4">
                        <button class="btn text-white rounded-pill px-4" style="background-color: #198754;">
                            <i class="bi bi-save me-1"></i> Guardar cambios
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>
