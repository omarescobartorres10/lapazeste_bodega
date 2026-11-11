{{-- ✅ Modal global Editar Producto --}}
<div class="modal fade"
     id="modalEditarProducto"
     tabindex="-1"
     aria-labelledby="modalEditarProductoLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">

            {{-- ============================================================
                ENCABEZADO
            ============================================================ --}}
            <div class="modal-header bg-warning text-white rounded-top-4">
                <h5 class="modal-title" id="modalEditarProductoLabel">
                    <i class="bi bi-pencil-square me-2"></i>
                    Editar Producto
                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">
                </button>
            </div>

            {{-- ============================================================
                FORMULARIO
            ============================================================ --}}
            <form id="formEditarProducto" method="POST">
                @csrf
                @method('PUT')

                {{-- Página actual --}}
                <input
                    type="hidden"
                    name="page"
                    id="editPage"
                    value="{{ request('page', 1) }}">

                {{-- Mantener búsqueda actual --}}
                <input
                    type="hidden"
                    name="buscar"
                    id="editBuscar"
                    value="{{ request('buscar', '') }}">

                <div class="modal-body p-4">

                    <div class="row g-3">

                        {{-- ==================================================
                            CÓDIGO
                        ================================================== --}}
                        <div class="col-md-6">
                            <label for="editCodigoView" class="form-label fw-semibold">
                                Código <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                name="codigo"
                                id="editCodigoView"
                                class="form-control rounded-pill"
                                maxlength="255"
                                autocomplete="off"
                                required
                                style="text-transform: uppercase;"
                                placeholder="Ingrese el código">

                            <small class="text-muted">
                                El código debe ser único dentro de la bodega.
                            </small>
                        </div>

                        {{-- ==================================================
                            NOMBRE
                        ================================================== --}}
                        <div class="col-md-6">
                            <label for="editNombre" class="form-label fw-semibold">
                                Nombre <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                name="nombre"
                                id="editNombre"
                                class="form-control rounded-pill"
                                maxlength="255"
                                required>
                        </div>

                        {{-- ==================================================
                            DESCRIPCIÓN
                        ================================================== --}}
                        <div class="col-md-12">
                            <label for="editDescripcion" class="form-label fw-semibold">
                                Descripción
                            </label>

                            <textarea
                                name="descripcion"
                                id="editDescripcion"
                                class="form-control rounded-4"
                                rows="2"></textarea>
                        </div>

                        {{-- ==================================================
                            CATEGORÍA
                        ================================================== --}}
                        <div class="col-md-6">
                            <label for="editCategoria" class="form-label fw-semibold">
                                Categoría <span class="text-danger">*</span>
                            </label>

                            <select
                                name="categoria_id"
                                id="editCategoria"
                                class="form-select rounded-pill"
                                required>

                                <option value="">Seleccione...</option>

                                @foreach ($categorias as $cat)
                                    <option value="{{ $cat->id }}">
                                        {{ $cat->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- ==================================================
                            UNIDAD DE MEDIDA
                        ================================================== --}}
                        <div class="col-md-6">
                            <label for="editUnidad" class="form-label fw-semibold">
                                Unidad de medida <span class="text-danger">*</span>
                            </label>

                            <select
                                name="unidades_id"
                                id="editUnidad"
                                class="form-select rounded-pill"
                                required>

                                <option value="">Seleccione...</option>

                                @foreach ($unidades as $u)
                                    <option value="{{ $u->id }}">
                                        {{ $u->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- ==================================================
                            STOCK
                        ================================================== --}}
                        <div class="col-md-6">
                            <label for="editStock" class="form-label fw-semibold">
                                Stock <span class="text-danger">*</span>
                            </label>

                            <input
                                type="number"
                                name="stock"
                                id="editStock"
                                class="form-control rounded-pill"
                                min="0"
                                required>
                        </div>

                        {{-- ==================================================
                            TIPO PRODUCTO
                        ================================================== --}}
                        <div class="col-md-6">
                            <label for="editTipo" class="form-label fw-semibold">
                                Tipo de producto <span class="text-danger">*</span>
                            </label>

                            <select
                                name="tipo_producto_id"
                                id="editTipo"
                                class="form-select rounded-pill"
                                required>

                                <option value="">Seleccione...</option>

                                @foreach ($tipoProductos as $t)
                                    <option value="{{ $t->id }}">
                                        {{ $t->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- ==================================================
                            DEPARTAMENTO
                        ================================================== --}}
                        <div class="col-md-6">
                            <label for="editDepartamento" class="form-label fw-semibold">
                                Departamento <span class="text-danger">*</span>
                            </label>

                            <select
                                name="departamento_id"
                                id="editDepartamento"
                                class="form-select rounded-pill"
                                required>

                                <option value="">Seleccione...</option>

                                @foreach ($departamentos as $d)
                                    <option value="{{ $d->id }}">
                                        {{ $d->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- ==================================================
                            BODEGA
                            Solo administrador
                        ================================================== --}}
                        @if(!$bodega)
                            <div class="col-md-6">
                                <label for="editBodega" class="form-label fw-semibold">
                                    Bodega <span class="text-danger">*</span>
                                </label>

                                <select
                                    name="bodega_id"
                                    id="editBodega"
                                    class="form-select rounded-pill"
                                    required>

                                    <option value="">Seleccione...</option>

                                    @foreach ($bodegas as $bod)
                                        <option value="{{ $bod->id }}">
                                            {{ $bod->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                    </div>
                </div>

                {{-- ============================================================
                    BOTONES
                ============================================================ --}}
                <div class="modal-footer bg-light rounded-bottom-4">

                    <button
                        type="button"
                        class="btn btn-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">

                        <i class="bi bi-x-circle me-1"></i>
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        id="btnActualizarProducto"
                        class="btn btn-warning text-white rounded-pill px-4">

                        <i class="bi bi-save me-1"></i>
                        Guardar Cambios
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const formEditarProducto = document.getElementById("formEditarProducto");
    const btnActualizarProducto = document.getElementById("btnActualizarProducto");
    const modalEditarProducto = document.getElementById("modalEditarProducto");
    const editCodigoView = document.getElementById("editCodigoView");

    // ================================================================
    // CONVERTIR CÓDIGO A MAYÚSCULAS VISUALMENTE
    // ================================================================
    if (editCodigoView) {
        editCodigoView.addEventListener("input", function () {
            this.value = this.value.toUpperCase();
        });
    }

    // ================================================================
    // EVITAR DOBLE ENVÍO
    // ================================================================
    if (formEditarProducto && btnActualizarProducto) {

        formEditarProducto.addEventListener("submit", function (event) {

            const codigo = editCodigoView
                ? editCodigoView.value.trim()
                : '';

            if (!codigo) {
                event.preventDefault();

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Código requerido',
                        text: 'Debe ingresar un código para el producto.'
                    });
                } else {
                    alert('Debe ingresar un código para el producto.');
                }

                if (editCodigoView) {
                    editCodigoView.focus();
                }

                return;
            }

            editCodigoView.value = codigo.toUpperCase();

            btnActualizarProducto.disabled = true;

            btnActualizarProducto.innerHTML = `
                <span
                    class="spinner-border spinner-border-sm me-2"
                    role="status"
                    aria-hidden="true">
                </span>
                Guardando cambios...
            `;
        });
    }

    // ================================================================
    // RESTABLECER BOTÓN CADA VEZ QUE SE ABRE EL MODAL
    // ================================================================
    if (modalEditarProducto && btnActualizarProducto) {

        modalEditarProducto.addEventListener("show.bs.modal", function () {

            btnActualizarProducto.disabled = false;

            btnActualizarProducto.innerHTML = `
                <i class="bi bi-save me-1"></i>
                Guardar Cambios
            `;
        });
    }

});
</script>