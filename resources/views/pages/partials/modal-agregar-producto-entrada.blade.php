<div class="modal fade" id="modalAgregarProducto" tabindex="-1" aria-labelledby="modalAgregarProductoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            
            <div class="modal-header py-3" style="background-color: #157347; color: white; border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                <h5 class="modal-title fw-bold d-flex align-items-center" id="modalAgregarProductoLabel">
                    <i class="bi bi-box2-heart-fill me-2 fs-5"></i> Agregar Nuevo Producto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body bg-light">
                <form id="formAgregarProducto">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">
                                <i class="bi bi-upc-scan me-1 text-success"></i> Código
                            </label>
                            <input type="text" name="codigo" class="form-control rounded-pill" placeholder="EJEMPLO: A-001" maxlength="50" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">
                                <i class="bi bi-tag-fill me-1 text-success"></i> Nombre
                            </label>
                            <input type="text" name="nombre" class="form-control rounded-pill" placeholder="NOMBRE DEL PRODUCTO" maxlength="255" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">
                                <i class="bi bi-diagram-3 me-1 text-success"></i> Categoría
                            </label>
                            <select name="categoria_id" class="form-select rounded-pill" required>
                                <option value="">Selecciona una categoría</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}">{{ strtoupper($cat->nombre) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">
                                <i class="bi bi-building-check me-1 text-success"></i> Departamento
                            </label>
                            <select name="departamento_id" class="form-select rounded-pill" required>
                                <option value="">Selecciona un departamento</option>
                                @foreach($departamentos as $dep)
                                    <option value="{{ $dep->id }}">{{ strtoupper($dep->nombre) }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if(!$bodega)
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary">
                                    <i class="bi bi-shop me-1 text-success"></i> Bodega
                                </label>
                                <select name="bodega_id" id="bodega_modal" class="form-select rounded-pill" required>
                                    <option value="">Selecciona una bodega</option>
                                    @foreach($bodegas as $bod)
                                        <option value="{{ $bod->id }}">{{ strtoupper($bod->nombre) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">
                                <i class="bi bi-layers-half me-1 text-success"></i> Tipo de Producto
                            </label>
                            <select name="tipo_producto_id" class="form-select rounded-pill" required>
                                <option value="">Selecciona un tipo</option>
                                @foreach($tipoProductos as $tipo)
                                    <option value="{{ $tipo->id }}">{{ strtoupper($tipo->nombre) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">
                                <i class="bi bi-rulers me-1 text-success"></i> Unidad de Medida
                            </label>
                            <select name="unidades_id" class="form-select rounded-pill" required>
                                <option value="">Selecciona una unidad</option>
                                @foreach($unidades as $uni)
                                    <option value="{{ $uni->id }}">{{ strtoupper($uni->nombre) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary">
                                <i class="bi bi-journal-text me-1 text-success"></i> Descripción
                            </label>
                            <textarea name="descripcion" class="form-control rounded-4" rows="2" placeholder="DESCRIPCIÓN DEL PRODUCTO (OPCIONAL)"></textarea>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 me-2" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </button>

                        <button 
                            type="submit" 
                            id="btnGuardarProducto"
                            class="btn text-white rounded-pill px-4" 
                            style="background-color:#157347;">
                            <i class="bi bi-check2-square me-1"></i> Guardar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<style>
    .modal-content {
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {transform: translateY(-15px); opacity: 0;}
        to {transform: translateY(0); opacity: 1;}
    }

    select:focus, input:focus, textarea:focus {
        border-color: #157347 !important;
        box-shadow: 0 0 0 0.2rem rgba(21,115,71,0.25) !important;
    }

    label i {
        opacity: 0.85;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formAgregarProducto');
    const modalEl = document.getElementById('modalAgregarProducto');
    const bodegaSelect = document.getElementById('bodega-general');
    const modalBodegaSelect = document.getElementById('bodega_modal');
    const btnGuardarProducto = document.getElementById('btnGuardarProducto');

    const textoOriginalBoton = `
        <i class="bi bi-check2-square me-1"></i> Guardar Producto
    `;

    const bloquearBotonGuardar = () => {
        if (btnGuardarProducto) {
            btnGuardarProducto.disabled = true;
            btnGuardarProducto.style.pointerEvents = 'none';

            btnGuardarProducto.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Guardando...
            `;
        }
    };

    const restaurarBotonGuardar = () => {
        if (btnGuardarProducto) {
            btnGuardarProducto.disabled = false;
            btnGuardarProducto.style.pointerEvents = 'auto';
            btnGuardarProducto.innerHTML = textoOriginalBoton;
        }

        if (form) {
            form.dataset.enviando = '0';
        }
    };

    const openModal = () => {
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');

        let modalInstance = new bootstrap.Modal(modalEl);
        modalInstance.show();
    };

    document.querySelectorAll('[data-bs-target="#modalAgregarProducto"]').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            openModal();
        });
    });

    modalEl.addEventListener('hidden.bs.modal', () => {
        form.reset();
        restaurarBotonGuardar();
    });

    form.addEventListener('submit', e => {
        e.preventDefault();

        if (!form.checkValidity()) {
            return;
        }

        if (form.dataset.enviando === '1') {
            return;
        }

        form.dataset.enviando = '1';
        bloquearBotonGuardar();

        const formData = new FormData(form);
        const bodegaId = bodegaSelect?.value || modalBodegaSelect?.value || "{{ $bodega ? $bodega->id : '' }}";
        formData.append('bodega_id', bodegaId);

        fetch('{{ route("productos.crearEntrada") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Producto registrado',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    modalInstance.hide();

                    if (typeof window.actualizarListaConNuevoProducto === "function") {
                        window.actualizarListaConNuevoProducto(data.producto);
                    }
                });
            } else {
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Error', 
                    text: data.message 
                });

                restaurarBotonGuardar();
            }
        })
        .catch(() => {
            Swal.fire({ 
                icon: 'error', 
                title: 'Error', 
                text: 'No se pudo registrar el producto.' 
            });

            restaurarBotonGuardar();
        });
    });

});
</script>