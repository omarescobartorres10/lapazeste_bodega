@extends('welcome')

@section('title', 'Registro de Entradas')

@section('content')
<head>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<div class="container-fluid py-4">
    <h1 class="mb-4">📦 Registro de Entradas</h1>

    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header text-white fw-bold" style="background-color:#198754;">
            <i class="bi bi-box-arrow-in-down me-2"></i> Nueva entrada
        </div>

        <div class="card-body">
            <form id="form-entrada" method="POST" action="{{ route('entradas.store') }}" enctype="multipart/form-data">
                @csrf

                @if($bodega)
                    <div class="alert alert-info d-flex align-items-center mb-4 shadow-sm rounded-pill px-3 py-2">
                        <i class="bi bi-shop me-2 fs-5"></i>
                        <div><strong>Bodega asignada:</strong> {{ strtoupper($bodega->nombre) }}</div>
                        <input type="hidden" id="bodega-general" name="bodega_id" value="{{ $bodega->id }}">
                    </div>
                @else
                    <div class="alert alert-warning d-flex align-items-center mb-4 shadow-sm rounded-pill px-3 py-2">
                        <i class="bi bi-shield-lock me-2 fs-5"></i>
                        <div><strong>Modo administrador:</strong> Seleccione la bodega donde hará el ingreso.</div>
                    </div>
                @endif

                <div class="row mb-4 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-shop me-1 text-success"></i> Seleccione la bodega
                        </label>

                        @if($bodega)
                            <input type="text" class="form-control" value="{{ strtoupper($bodega->nombre) }}" readonly>
                        @else
                            <select id="bodega-general" name="bodega_id" class="form-select" required>
                                <option value="">Seleccionar bodega</option>
                                @foreach($bodegas as $bod)
                                    <option value="{{ $bod->id }}">{{ strtoupper($bod->nombre) }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-building me-1 text-primary"></i> Departamento
                        </label>
                        <select id="departamento-general" name="departamento_id" class="form-select" required>
                            <option value="">Seleccionar departamento</option>
                            @foreach($departamentos as $departamento)
                                <option value="{{ $departamento->id }}">{{ strtoupper($departamento->nombre) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="productos-container" class="border rounded-3 p-3 bg-light">
                    <div class="producto-item row align-items-end g-3">
                        <input type="hidden" name="productos[0][departamento_id]" class="departamento-hidden">

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Categoría</label>
                            <select name="productos[0][categoria_id]" class="form-select categoria" required disabled>
                                <option value="">Seleccionar</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Producto</label>
                            <select name="productos[0][producto_id]" class="form-select producto" required disabled>
                                <option value="">Seleccionar</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Cantidad</label>
                            <input type="number" name="productos[0][cantidad]" class="form-control" min="1" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Observación</label>
                            <input type="text" name="productos[0][observacion]" class="form-control">
                        </div>

                        <div class="col-md-1 text-center">
                            <button type="button" class="btn btn-danger btn-remove">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <button type="button" id="btn-add" class="btn btn-outline-success">
                        <i class="bi bi-plus-circle"></i> Agregar producto
                    </button>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Entregado por</label>
                        <input type="text" name="entregado_por" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Recibido por</label>
                        <input type="text" name="recibido_por" class="form-control" required>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Número de Factura</label>
                        <input type="text" name="numero_factura" class="form-control" maxlength="255" value="{{ old('numero_factura') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Número de Orden de Compra</label>
                        <input type="text" name="numero_orden_compra" class="form-control" maxlength="255" value="{{ old('numero_orden_compra') }}" required>
                    </div>
                </div>


                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarProducto">
                        <i class="bi bi-plus-lg me-1"></i> Nuevo producto
                    </button>

                    <button 
                        type="submit" 
                        id="btnGuardarEntrada"
                        class="btn text-white" 
                        style="background-color:#198754;">
                        <i class="bi bi-check-circle me-1"></i> Guardar Entradas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('pages.partials.modal-agregar-producto-entrada', [
    'bodega' => $bodega,
    'categorias' => $categorias,
    'departamentos' => $departamentos,
    'bodegas' => $bodegas,
    'tipoProductos' => \App\Models\TipoProducto::where('estado', 1)->get(),
    'unidades' => \App\Models\UnidadMedida::where('estado', 1)->get(),
])
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
#loader-overlay {
    position: fixed;
    top: 0; 
    left: 0;
    width: 100%; 
    height: 100%;
    background: rgba(255,255,255,0.9);
    z-index: 9999;
    display: none;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    color: #198754;
    font-weight: 600;
}
.spinner-border { 
    width: 3rem; 
    height: 3rem; 
    color: #198754; 
}
</style>

<div id="loader-overlay">
    <div class="spinner-border" role="status"></div>
    <div class="mt-3">Cargando productos...</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: '{{ session('success') }}',
            showConfirmButton: false,
            timer: 2500
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}',
            showConfirmButton: true
        });
    @endif

    @if ($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Error de validación',
            html: `{!! implode('<br>', $errors->all()) !!}`,
            showConfirmButton: true
        });
    @endif
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let index = 1;

    const container = document.getElementById('productos-container');
    const btnAdd = document.getElementById('btn-add');
    const bodegaSelect = document.getElementById('bodega-general');
    const deptoSelect = document.getElementById('departamento-general');
    const formEntrada = document.getElementById('form-entrada');
    const btnGuardarEntrada = document.getElementById('btnGuardarEntrada');

    let productosGlobal = [];
    let productosSeleccionados = new Set();

    function bloquearBotonGuardar() {
        btnGuardarEntrada.disabled = true;
        btnGuardarEntrada.style.pointerEvents = 'none';
        btnGuardarEntrada.innerHTML = `
            <span 
                class="spinner-border spinner-border-sm me-2" 
                role="status" 
                aria-hidden="true"
                style="width: 1rem; height: 1rem;">
            </span>
            Guardando...
        `;
    }

    function restaurarBotonGuardar() {
        formEntrada.dataset.enviando = '0';
        btnGuardarEntrada.disabled = false;
        btnGuardarEntrada.style.pointerEvents = 'auto';
        btnGuardarEntrada.innerHTML = `
            <i class="bi bi-check-circle me-1"></i> Guardar Entradas
        `;
    }

    function llenarCategoriasEnSelect(selectCategoria) {
        selectCategoria.innerHTML = '<option value="">Seleccionar</option>';

        if (!deptoSelect.value || productosGlobal.length === 0) {
            selectCategoria.disabled = true;
            return;
        }

        const categoriasMap = {};

        productosGlobal.forEach(p => {
            if (String(p.departamento_id) === String(deptoSelect.value)) {
                categoriasMap[p.categoria_id] = p.categoria_nombre;
            }
        });

        Object.entries(categoriasMap).forEach(([id, nombre]) => {
            const opt = document.createElement('option');
            opt.value = id;
            opt.textContent = nombre;
            selectCategoria.appendChild(opt);
        });

        selectCategoria.disabled = false;
    }

    function actualizarCategorias() {
        document.querySelectorAll('.categoria').forEach(select => {
            llenarCategoriasEnSelect(select);
        });

        document.querySelectorAll('.producto').forEach(select => {
            select.innerHTML = '<option value="">Seleccionar</option>';
            select.disabled = true;
            select.removeAttribute('data-prev-value');
        });

        productosSeleccionados.clear();

        document.querySelectorAll('.departamento-hidden').forEach(inp => {
            inp.value = deptoSelect.value;
        });
    }

    function limpiarFormularioProductos() {
        const first = container.firstElementChild;

        while (container.children.length > 1) {
            container.lastElementChild.remove();
        }

        first.querySelectorAll('input[type="text"], input[type="number"]').forEach(inp => {
            inp.value = '';
        });

        first.querySelectorAll('.departamento-hidden').forEach(inp => {
            inp.value = deptoSelect.value || '';
        });

        first.querySelectorAll('.categoria').forEach(select => {
            select.innerHTML = '<option value="">Seleccionar</option>';
            select.disabled = true;
        });

        first.querySelectorAll('.producto').forEach(select => {
            select.innerHTML = '<option value="">Seleccionar</option>';
            select.disabled = true;
            select.removeAttribute('data-prev-value');
        });

        productosSeleccionados.clear();
    }

    async function cargarProductos(bodegaId) {
        if (!bodegaId) {
            productosGlobal = [];
            limpiarFormularioProductos();
            return;
        }

        document.getElementById('loader-overlay').style.display = 'flex';

        try {
            const res = await fetch(`/bodega/${bodegaId}/productos`);
            productosGlobal = await res.json();

            if (deptoSelect.value) {
                actualizarCategorias();
            }
        } catch {
            Swal.fire({ 
                icon: 'error', 
                title: 'Error', 
                text: 'No se pudieron cargar los productos.' 
            });
        } finally {
            document.getElementById('loader-overlay').style.display = 'none';
        }
    }

    function actualizarOpcionesProductos() {
        document.querySelectorAll('.producto').forEach(select => {
            const item = select.closest('.producto-item');
            const categoriaId = item.querySelector('.categoria').value;
            const currentValue = select.dataset.prevValue ? parseInt(select.dataset.prevValue) : null;

            if (!categoriaId) {
                select.innerHTML = '<option value="">Seleccionar</option>';
                select.disabled = true;
                return;
            }

            const filtrados = productosGlobal.filter(p =>
                String(p.departamento_id) === String(deptoSelect.value) &&
                String(p.categoria_id) === String(categoriaId) &&
                (!productosSeleccionados.has(p.id) || p.id === currentValue)
            );

            select.innerHTML = '<option value="">Seleccionar</option>';

            filtrados.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = `${p.nombre} | Stock: ${p.stock}`;
                select.appendChild(opt);
            });

            if (currentValue) {
                select.value = currentValue;
            }

            select.disabled = false;
        });
    }

    formEntrada.addEventListener('submit', function (e) {
        if (formEntrada.dataset.enviando === '1') {
            e.preventDefault();
            return false;
        }

        document.querySelectorAll('.departamento-hidden').forEach(inp => {
            inp.value = deptoSelect.value;
        });

        if (!bodegaSelect.value || !deptoSelect.value) {
            e.preventDefault();

            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Debe seleccionar bodega y departamento antes de guardar.'
            });

            return false;
        }

        let productosValidos = true;

        document.querySelectorAll('.producto-item').forEach(item => {
            const categoria = item.querySelector('.categoria');
            const producto = item.querySelector('.producto');
            const cantidad = item.querySelector('input[name*="[cantidad]"]');

            if (!categoria.value || !producto.value || !cantidad.value || parseInt(cantidad.value) <= 0) {
                productosValidos = false;
            }
        });

        if (!productosValidos) {
            e.preventDefault();

            Swal.fire({
                icon: 'warning',
                title: 'Datos incompletos',
                text: 'Debe seleccionar categoría, producto y cantidad en cada fila.'
            });

            return false;
        }

        document.querySelectorAll('select:disabled').forEach(sel => {
            sel.disabled = false;
        });

        if (!formEntrada.checkValidity()) {
            e.preventDefault();
            formEntrada.reportValidity();
            return false;
        }

        formEntrada.dataset.enviando = '1';
        bloquearBotonGuardar();

        return true;
    });

    if (bodegaSelect && bodegaSelect.value) {
        cargarProductos(bodegaSelect.value);
    }

    bodegaSelect?.addEventListener('change', () => {
        limpiarFormularioProductos();
        cargarProductos(bodegaSelect.value);
    });

    deptoSelect.addEventListener('change', () => {
        actualizarCategorias();
    });

    btnAdd.addEventListener('click', () => {
        if (!bodegaSelect.value || !deptoSelect.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Seleccione bodega y departamento antes de agregar productos.'
            });
            return;
        }

        const clone = container.firstElementChild.cloneNode(true);

        clone.querySelectorAll('input, select').forEach(el => {
            const oldName = el.getAttribute('name');

            if (oldName) {
                el.setAttribute('name', oldName.replace(/\[\d+\]/, `[${index}]`));
            }

            if (el.tagName === 'INPUT') {
                el.value = '';
            }
        });

        const catSelect = clone.querySelector('.categoria');
        const prodSelect = clone.querySelector('.producto');

        catSelect.innerHTML = '<option value="">Seleccionar</option>';
        prodSelect.innerHTML = '<option value="">Seleccionar</option>';
        prodSelect.disabled = true;
        prodSelect.removeAttribute('data-prev-value');

        clone.querySelector('.departamento-hidden').value = deptoSelect.value;

        llenarCategoriasEnSelect(catSelect);

        container.appendChild(clone);
        index++;
    });

    container.addEventListener('click', e => {
        if (e.target.closest('.btn-remove') && container.children.length > 1) {
            const item = e.target.closest('.producto-item');
            const select = item.querySelector('.producto');
            const value = parseInt(select.value);

            if (value && productosSeleccionados.has(value)) {
                productosSeleccionados.delete(value);
            }

            item.remove();
            actualizarOpcionesProductos();
        }
    });

    container.addEventListener('change', e => {
        const item = e.target.closest('.producto-item');

        if (!item) return;

        const productoSelect = item.querySelector('.producto');

        if (e.target.classList.contains('categoria')) {
            const prevProd = parseInt(productoSelect.dataset.prevValue);

            if (prevProd && productosSeleccionados.has(prevProd)) {
                productosSeleccionados.delete(prevProd);
            }

            productoSelect.removeAttribute('data-prev-value');

            const categoriaId = e.target.value;

            productoSelect.innerHTML = '<option value="">Seleccionar</option>';
            productoSelect.disabled = !categoriaId;

            const filtrados = productosGlobal.filter(p =>
                String(p.departamento_id) === String(deptoSelect.value) &&
                String(p.categoria_id) === String(categoriaId) &&
                !productosSeleccionados.has(p.id)
            );

            filtrados.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = `${p.nombre} | Stock: ${p.stock}`;
                productoSelect.appendChild(opt);
            });
        }

        if (e.target.classList.contains('producto')) {
            const selectedValue = parseInt(productoSelect.value);
            const prevValue = productoSelect.dataset.prevValue ? parseInt(productoSelect.dataset.prevValue) : null;

            if (prevValue && productosSeleccionados.has(prevValue)) {
                productosSeleccionados.delete(prevValue);
            }

            if (selectedValue) {
                productosSeleccionados.add(selectedValue);
                productoSelect.dataset.prevValue = selectedValue;
            } else {
                productoSelect.removeAttribute('data-prev-value');
            }

            actualizarOpcionesProductos();
        }
    });

    window.actualizarListaConNuevoProducto = function(nuevoProducto) {
        productosGlobal.push(nuevoProducto);

        if (deptoSelect.value) {
            actualizarCategorias();
        }

        Swal.fire({
            icon: 'success',
            title: 'Producto agregado',
            text: 'El nuevo producto se añadió y ya está disponible para seleccionar.',
            timer: 1800,
            showConfirmButton: false
        });
    };
});
</script>
@endpush
