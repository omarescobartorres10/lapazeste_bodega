<!-- Modal AGREGAR PRODUCTO -->
<div class="modal fade" id="modalAgregarProducto" tabindex="-1" aria-labelledby="modalAgregarProductoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow rounded-4 border-0">
            <div class="modal-header text-white rounded-top-4" style="background-color: #198754;">
                <h5 class="modal-title fw-bold d-flex align-items-center" id="modalAgregarProductoLabel">
                    <i class="bi bi-plus-circle me-2"></i>Agregar Nuevo Producto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formAgregarProducto" action="{{ route('productos.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Código</label>
                            <input 
                                type="text" 
                                name="codigo" 
                                class="form-control rounded-pill px-4 text-uppercase" 
                                placeholder="Ejemplo: A-001" 
                                required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Nombre</label>
                            <input 
                                type="text" 
                                name="nombre" 
                                class="form-control rounded-pill px-4 text-uppercase" 
                                placeholder="Nombre del producto" 
                                required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Categoría</label>
                            <select name="categoria_id" class="form-select rounded-pill px-4" required>
                                <option value="">Selecciona una categoría</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">
                                        {{ strtoupper($categoria->nombre) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Departamento</label>
                            <select name="departamento_id" class="form-select rounded-pill px-4" required>
                                <option value="">Selecciona un departamento</option>
                                @foreach ($departamentos as $departamento)
                                    <option value="{{ $departamento->id }}">
                                        {{ strtoupper($departamento->nombre) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 🔹 Bodega según tipo de usuario --}}
                        @if($bodega)
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Bodega asignada</label>
                                <input 
                                    type="text" 
                                    class="form-control rounded-pill px-4 bg-light" 
                                    value="{{ $bodega->nombre }}" 
                                    readonly>
                                <input type="hidden" name="bodega_id" value="{{ $bodega->id }}">
                            </div>
                        @else
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Bodega</label>
                                <select name="bodega_id" class="form-select rounded-pill px-4" required>
                                    <option value="">Selecciona una bodega</option>
                                    @foreach ($bodegas as $bod)
                                        <option value="{{ $bod->id }}">
                                            {{ strtoupper($bod->nombre) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Tipo de Producto</label>
                            <select name="tipo_producto_id" class="form-select rounded-pill px-4" required>
                                <option value="">Selecciona un tipo</option>
                                @foreach ($tipoProductos as $tipo)
                                    <option value="{{ $tipo->id }}">
                                        {{ strtoupper($tipo->nombre) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Unidad de Medida</label>
                            <select name="unidades_id" class="form-select rounded-pill px-4" required>
                                <option value="">Selecciona una unidad</option>
                                @foreach ($unidades as $unidad)
                                    <option value="{{ $unidad->id }}">
                                        {{ strtoupper($unidad->nombre) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Stock Inicial</label>
                            <input 
                                type="number" 
                                name="stock" 
                                class="form-control rounded-pill px-4" 
                                min="0" 
                                placeholder="Ejemplo: 10" 
                                required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">Descripción</label>
                            <textarea 
                                name="descripcion" 
                                class="form-control rounded-3 px-4 text-uppercase" 
                                rows="2" 
                                placeholder="Descripción del producto (opcional)"></textarea>
                        </div>
                    </div>

                    <div class="text-end">
                        <button 
                            type="submit" 
                            id="btnGuardarProducto"
                            class="btn text-white rounded-pill px-4" 
                            style="background-color: #198754;">
                            <i class="bi bi-save me-1"></i>Guardar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- FIN MODAL AGREGAR -->


<script>
document.addEventListener("DOMContentLoaded", function () {
    const formAgregarProducto = document.getElementById("formAgregarProducto");
    const btnGuardarProducto = document.getElementById("btnGuardarProducto");

    if (formAgregarProducto && btnGuardarProducto) {
        formAgregarProducto.addEventListener("submit", function () {
            btnGuardarProducto.disabled = true;

            btnGuardarProducto.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Guardando...
            `;
        });
    }
});
</script>