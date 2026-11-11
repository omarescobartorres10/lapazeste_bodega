{{-- 🔹 Modal Consulta Global (otras bodegas) --}}
<div class="modal fade" id="modalConsultaGlobal" tabindex="-1" aria-labelledby="modalConsultaGlobalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-eye me-2"></i> Consulta de Existencias en Otras Bodegas
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                {{-- 🔹 Filtros --}}
                <div class="row mb-3 g-3 align-items-center">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Buscar producto:</label>
                        <input type="text" id="buscarConsulta" class="form-control rounded-pill" placeholder="Nombre o código...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Filtrar por bodega:</label>
                        <select id="filtroBodegaConsulta" class="form-select rounded-pill">
                            <option value="">Todas las bodegas</option>
                            @foreach ($bodegas as $bod)
                                @if(!$bodega || $bod->id !== $bodega->id)
                                    <option value="{{ $bod->id }}">{{ strtoupper($bod->nombre) }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 text-end">
                        <button id="btnBuscarConsulta" class="btn btn-primary rounded-pill mt-3">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </div>

                <div id="consultaLoader" class="text-center my-3" style="display:none;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-primary fw-semibold">Cargando datos...</p>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle text-center" id="tablaConsultaGlobal">
                        <thead class="table-primary">
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Bodega</th>
                                <th>Stock</th>
                                <th>Unidad</th>
                                <th>Categoría</th>
                                <th>Departamento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="8" class="text-muted">Usa los filtros para mostrar productos...</td></tr>
                        </tbody>
                    </table>
                </div>

                <div id="consultaPaginacion" class="d-flex justify-content-center mt-3"></div>
            </div>

            <div class="modal-footer bg-light rounded-bottom-4">
                <button class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- 🔹 Fin Modal Consulta Global -->