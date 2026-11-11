@extends('welcome')

@section('title', 'Registro de Salidas')

@section('content')
<head>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        #loader-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(255,255,255,0.9);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            color: #dc3545;
            font-weight: 600;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
            color: #dc3545;
        }
    </style>
</head>

<div class="container-fluid py-4">
    <h1 class="mb-4">📤 Registro de Salidas</h1>

    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header text-white fw-bold" style="background-color:#dc3545;">
            <i class="bi bi-box-arrow-up me-2"></i> Nueva salida
        </div>

        <div class="card-body">
            <form id="form-salida" method="POST" action="{{ route('salidas.store') }}">
                @csrf

                {{-- 🔹 Bodega asignada o seleccionable --}}
                @if($bodega)
                    <div class="alert alert-info d-flex align-items-center mb-4 shadow-sm rounded-pill px-3 py-2">
                        <i class="bi bi-shop me-2 fs-5"></i>
                        <div><strong>Bodega asignada:</strong> {{ strtoupper($bodega->nombre) }}</div>
                        <input type="hidden" id="bodega-general" name="bodega_id" value="{{ $bodega->id }}">
                    </div>
                @else
                    <div class="alert alert-warning d-flex align-items-center mb-4 shadow-sm rounded-pill px-3 py-2">
                        <i class="bi bi-shield-lock me-2 fs-5"></i>
                        <div><strong>Modo administrador:</strong> Seleccione la bodega donde hará la salida.</div>
                    </div>
                @endif

                {{-- 🔹 Selección de bodega y departamento --}}
                <div class="row mb-4 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-shop me-1 text-danger"></i> Seleccione la bodega
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

                {{-- 🔹 Contenedor dinámico de productos --}}
                <div id="productos-container" class="border rounded-3 p-3 bg-light">
                    <div class="producto-item row align-items-end g-3">
                        <input type="hidden" name="productos[0][bodega_id]" class="bodega-hidden" value="{{ $bodega ? $bodega->id : '' }}">
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

                {{-- Botón agregar fila --}}
                <div class="text-end mt-3">
                    <button type="button" id="btn-add" class="btn btn-outline-danger">
                        <i class="bi bi-plus-circle"></i> Agregar producto
                    </button>
                </div>

                {{-- 🔹 Entregado / Recibido / Destino --}}
                <div class="row mt-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Entregado por</label>
                        <input type="text" name="entregado_por" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Recibido por</label>
                        <input type="text" name="recibido_por" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Destino</label>
                        <input type="text" name="destino" class="form-control" required placeholder="Ej. Taller, Oficina, Proyecto...">
                    </div>
                </div>

                <div class="text-end mt-3">
                    <button 
                        type="submit" 
                        id="btnGuardarSalida"
                        class="btn text-white" 
                        style="background-color:#dc3545;">
                        <i class="bi bi-check-circle me-1"></i> Guardar Salida
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Overlay de carga --}}
<div id="loader-overlay">
    <div class="spinner-border" role="status"></div>
    <div class="mt-3">Cargando productos...</div>
</div>

{{-- ======================= --}}
{{--   SCRIPTS               --}}
{{-- ======================= --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    let index = 1;
    const container      = document.getElementById('productos-container');
    const btnAdd         = document.getElementById('btn-add');
    const bodegaSelect   = document.getElementById('bodega-general');
    const deptoSelect    = document.getElementById('departamento-general');
    const formSalida     = document.getElementById('form-salida');
    const btnGuardarSalida = document.getElementById('btnGuardarSalida');

    let productosGlobal  = [];
    let productosSeleccionados = new Set();

    /* ===============================
       VALIDACIÓN ANTES DE ENVIAR
       =============================== */
    formSalida.addEventListener('submit', e => {

        if (formSalida.dataset.enviando === '1') {
            e.preventDefault();
            return false;
        }

        if (!bodegaSelect.value || !deptoSelect.value) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Debe seleccionar bodega y departamento antes de guardar.'
            });
            return false;
        }

        // Setear departamento en los inputs hidden
        document.querySelectorAll('.departamento-hidden').forEach(inp => {
            inp.value = deptoSelect.value;
        });

        // Habilitar selects deshabilitados antes de enviar
        document.querySelectorAll('select:disabled').forEach(sel => {
            sel.disabled = false;
        });

        if (!formSalida.checkValidity()) {
            e.preventDefault();
            formSalida.reportValidity();
            return false;
        }

        formSalida.dataset.enviando = '1';

        btnGuardarSalida.disabled = true;
        btnGuardarSalida.style.pointerEvents = 'none';
        btnGuardarSalida.innerHTML = `
            <span 
                class="spinner-border spinner-border-sm me-2" 
                role="status" 
                aria-hidden="true"
                style="width: 1rem; height: 1rem;">
            </span>
            Guardando...
        `;

        return true;
    });

    /* ===============================
       CARGAR PRODUCTOS POR BODEGA
       =============================== */
    async function cargarProductos(bodegaId) {
        if (!bodegaId) return;

        document.getElementById('loader-overlay').style.display = 'flex';

        try {
            const res = await fetch(`/bodega/${bodegaId}/productos`);
            productosGlobal = await res.json();
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar los productos.'
            });
        } finally {
            document.getElementById('loader-overlay').style.display = 'none';

            if (deptoSelect.value) {
                actualizarCategorias();
            }
        }
    }

    /* ===============================
       LIMPIAR FORMULARIO PRINCIPAL
       =============================== */
    function limpiarFormulario() {
        const first = container.firstElementChild;
        while (container.children.length > 1) {
            container.lastElementChild.remove();
        }

        first.querySelectorAll('select').forEach(sel => {
            sel.innerHTML = '<option value="">Seleccionar</option>';
            if (sel.classList.contains('categoria') || sel.classList.contains('producto')) {
                sel.disabled = true;
            }
        });

        first.querySelectorAll('input[type="text"], input[type="number"]').forEach(inp => inp.value = '');
        first.querySelector('.bodega-hidden').value = bodegaSelect.value || '';
        first.querySelector('.departamento-hidden').value = deptoSelect.value || '';

        productosSeleccionados.clear();
    }

    if (bodegaSelect && bodegaSelect.value) {
        cargarProductos(bodegaSelect.value);
    }

    if (bodegaSelect) {
        bodegaSelect.addEventListener('change', () => {
            limpiarFormulario();
            if (bodegaSelect.value) {
                cargarProductos(bodegaSelect.value);
            }
        });
    }

    btnAdd.addEventListener('click', () => {

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

        const catSelect  = clone.querySelector('.categoria');
        const prodSelect = clone.querySelector('.producto');

        catSelect.innerHTML  = '<option value="">Seleccionar</option>';
        prodSelect.innerHTML = '<option value="">Seleccionar</option>';
        prodSelect.disabled  = true;
        prodSelect.removeAttribute('data-prev-value');

        clone.querySelector('.bodega-hidden').value       = bodegaSelect.value || '';
        clone.querySelector('.departamento-hidden').value = deptoSelect.value || '';

        if (deptoSelect.value) {
            const categoriasMap = {};
            productosGlobal.forEach(p => {
                if (p.departamento_id == deptoSelect.value) {
                    categoriasMap[p.categoria_id] = p.categoria_nombre;
                }
            });

            for (const [id, nombre] of Object.entries(categoriasMap)) {
                const opt = document.createElement('option');
                opt.value = id;
                opt.textContent = nombre;
                catSelect.appendChild(opt);
            }
            catSelect.disabled = false;
        }

        container.appendChild(clone);
        index++;
        actualizarOpcionesProductos();
    });

    container.addEventListener('click', e => {

        if (e.target.closest('.btn-remove') && container.children.length > 1) {
            const item   = e.target.closest('.producto-item');
            const select = item.querySelector('.producto');
            const value  = parseInt(select.value);

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

        const productoSelect  = item.querySelector('.producto');

        if (e.target.classList.contains('categoria')) {
            const categoriaId = e.target.value;
            productoSelect.innerHTML = '<option value="">Seleccionar</option>';
            productoSelect.disabled  = !categoriaId;

            const filtrados = productosGlobal.filter(p =>
                p.departamento_id == deptoSelect.value &&
                p.categoria_id == categoriaId &&
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
            const prevValue     = productoSelect.dataset.prevValue ? parseInt(productoSelect.dataset.prevValue) : null;

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

    deptoSelect.addEventListener('change', () => {
        actualizarCategorias();
        document.querySelectorAll('.departamento-hidden').forEach(inp => {
            inp.value = deptoSelect.value;
        });
    });

    function actualizarCategorias() {

        document.querySelectorAll('.categoria').forEach(select => {
            select.innerHTML = '<option value="">Seleccionar</option>';
            select.disabled  = false;

            const categoriasMap = {};
            productosGlobal.forEach(p => {
                if (p.departamento_id == deptoSelect.value) {
                    categoriasMap[p.categoria_id] = p.categoria_nombre;
                }
            });

            for (const [id, nombre] of Object.entries(categoriasMap)) {
                const opt = document.createElement('option');
                opt.value = id;
                opt.textContent = nombre;
                select.appendChild(opt);
            }
        });

        productosSeleccionados.clear();
        document.querySelectorAll('.producto').forEach(select => {
            select.innerHTML = '<option value="">Seleccionar</option>';
            select.disabled  = true;
            select.removeAttribute('data-prev-value');
        });
    }

    function actualizarOpcionesProductos() {

        document.querySelectorAll('.producto').forEach(select => {

            const item        = select.closest('.producto-item');
            const categoriaId = item.querySelector('.categoria').value;
            const currentValue = select.dataset.prevValue
                ? parseInt(select.dataset.prevValue)
                : (select.value ? parseInt(select.value) : null);

            if (!categoriaId) return;

            const filtrados = productosGlobal.filter(p =>
                p.departamento_id == deptoSelect.value &&
                p.categoria_id == categoriaId &&
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
        });
    }

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: '{{ session('success') }}',
            timer: 2500,
            showConfirmButton: false
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

});
</script>
@endsection