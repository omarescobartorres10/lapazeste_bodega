@extends('welcome')

@section('title', 'Registro de Préstamos')

@section('content')
<head>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
</head>

<div class="container-fluid py-4">
    <h1 class="mb-4">📤 Registro de Préstamos</h1>

    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header text-white fw-bold" style="background-color:#6f42c1;">
            <i class="bi bi-arrow-left-right me-2"></i> Nuevo préstamo
        </div>

        <div class="card-body">
            <form id="form-prestamo" method="POST" action="{{ route('prestamos.store') }}">
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
                        <div><strong>Modo administrador:</strong> Seleccione la bodega desde donde se hará el préstamo.</div>
                    </div>
                @endif

                <div class="row mb-4 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-shop me-1 text-purple"></i> Seleccione la bodega
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
                        <select id="departamento-general" class="form-select" required>
                            <option value="">Seleccionar departamento</option>
                            @foreach($departamentos as $dpto)
                                <option value="{{ $dpto->id }}">{{ strtoupper($dpto->nombre) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="prestamos-container" class="border rounded-3 p-3 bg-light">
                    <div class="prestamo-item row align-items-end g-3">
                        <input type="hidden" name="items[0][bodega_id]" class="bodega-hidden" value="{{ $bodega ? $bodega->id : '' }}">
                        <input type="hidden" name="items[0][departamento_id]" class="departamento-hidden">

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Categoría</label>
                            <select name="items[0][categoria_id]" class="form-select categoria" required disabled>
                                <option value="">Seleccionar</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Producto</label>
                            <select name="items[0][producto_id]" class="form-select producto" required disabled>
                                <option value="">Seleccionar</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Cantidad</label>
                            <input type="number" name="items[0][cantidad]" class="form-control" min="1" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Observación</label>
                            <input type="text" name="items[0][observacion]" class="form-control">
                        </div>

                        <div class="col-md-1 text-center">
                            <button type="button" class="btn btn-danger btn-remove">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <button type="button" id="btn-add" class="btn btn-outline-primary">
                        <i class="bi bi-plus-circle"></i> Agregar producto
                    </button>
                </div>

                <div class="row mt-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Entregado por</label>
                        <input type="text" name="entregado_por" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Recibido por</label>
                        <input type="text" name="recibido_por" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Destino</label>
                        <input type="text" name="destino" class="form-control" required placeholder="Ej. Taller, Proyecto, Oficina...">
                    </div>
                </div>

                <div class="text-end mt-3">
                    <button 
                        type="submit" 
                        id="btnRegistrarPrestamo"
                        class="btn text-white" 
                        style="background-color:#6f42c1;">
                        <i class="bi bi-check-circle me-1"></i> Registrar Préstamo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
    color: #6f42c1;
    font-weight: 600;
}
.spinner-border { width: 3rem; height: 3rem; color: #6f42c1; }
</style>

<div id="loader-overlay">
    <div class="spinner-border" role="status"></div>
    <div class="mt-3">Cargando productos...</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    let index = 1;
    const container      = document.getElementById('prestamos-container');
    const btnAdd         = document.getElementById('btn-add');
    const bodegaSelect   = document.getElementById('bodega-general');
    const deptoSelect    = document.getElementById('departamento-general');
    const formPrestamo   = document.getElementById('form-prestamo');
    const btnRegistrarPrestamo = document.getElementById('btnRegistrarPrestamo');

    let productosGlobal  = [];
    let productosUsados  = new Set();

    function showLoader() { 
        document.getElementById('loader-overlay').style.display = 'flex'; 
    }

    function hideLoader() { 
        document.getElementById('loader-overlay').style.display = 'none'; 
    }

    async function cargarProductos(bodegaId) {
        if (!bodegaId) return;

        showLoader();

        try {
            const res  = await fetch(`/prestamos/productos/bodega/${bodegaId}`);
            const data = await res.json();
            productosGlobal = data;
        } catch (e) {
            Swal.fire('Error', 'No se pudieron cargar los productos.', 'error');
        } finally {
            hideLoader();

            if (deptoSelect.value) {
                actualizarCategorias();
            }
        }
    }

    function actualizarCategorias() {
        const dptoId = deptoSelect.value;

        container.querySelectorAll('.categoria').forEach(sel => {
            sel.innerHTML = '<option value="">Seleccionar</option>';
            sel.disabled  = !dptoId;

            if (!dptoId) return;

            const categoriasMap = {};

            productosGlobal.forEach(p => {
                if (p.departamento_id == dptoId) {
                    categoriasMap[p.categoria_id] = p.categoria_nombre;
                }
            });

            for (const [id, nombre] of Object.entries(categoriasMap)) {
                const opt = document.createElement('option');
                opt.value = id;
                opt.textContent = nombre.toUpperCase();
                sel.appendChild(opt);
            }
        });

        document.querySelectorAll('.departamento-hidden').forEach(inp => inp.value = dptoId);
    }

    function actualizarOpcionesProductos() {
        const dptoId = deptoSelect.value;

        container.querySelectorAll('.producto').forEach(select => {
            const itemRow   = select.closest('.prestamo-item');
            const categoria = itemRow.querySelector('.categoria').value;

            const currentValue = select.value ? parseInt(select.value) : null;

            if (!categoria || !dptoId) return;

            const filtrados = productosGlobal.filter(p =>
                p.departamento_id == dptoId &&
                p.categoria_id    == categoria &&
                (!productosUsados.has(p.id) || p.id === currentValue)
            );

            select.innerHTML = '<option value="">Seleccionar</option>';

            filtrados.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = `${(p.codigo ?? '').toUpperCase()} | ${p.nombre.toUpperCase()} | Stock: ${p.stock}`;
                select.appendChild(opt);
            });

            if (currentValue) {
                select.value = currentValue;
            }
        });
    }

    if (bodegaSelect && bodegaSelect.value) {
        cargarProductos(bodegaSelect.value);
    }

    bodegaSelect?.addEventListener('change', () => {
        productosUsados.clear();

        const first = container.firstElementChild;

        while (container.children.length > 1) {
            container.lastElementChild.remove();
        }

        first.querySelectorAll('select').forEach(sel => {
            sel.innerHTML = '<option value="">Seleccionar</option>';
            sel.disabled  = sel.classList.contains('categoria') || sel.classList.contains('producto');
        });

        first.querySelectorAll('input[type="text"],input[type="number"]').forEach(inp => inp.value = '');
        first.querySelector('.bodega-hidden').value = bodegaSelect.value;

        index = 1;

        if (bodegaSelect.value) {
            cargarProductos(bodegaSelect.value);
        }
    });

    deptoSelect.addEventListener('change', () => {
        actualizarCategorias();
        actualizarOpcionesProductos();
    });

    btnAdd.addEventListener('click', () => {
        const clone = container.firstElementChild.cloneNode(true);

        clone.querySelectorAll('input,select').forEach(el => {
            const oldName = el.getAttribute('name');

            if (oldName) {
                el.setAttribute('name', oldName.replace(/\[\d+\]/, `[${index}]`));
            }

            if (el.tagName === 'INPUT') {
                el.value = '';
            }
        });

        const catSelect   = clone.querySelector('.categoria');
        const prodSelect  = clone.querySelector('.producto');

        catSelect.innerHTML  = '<option value="">Seleccionar</option>';
        prodSelect.innerHTML = '<option value="">Seleccionar</option>';
        prodSelect.disabled  = true;

        clone.querySelector('.bodega-hidden').value = bodegaSelect.value;
        clone.querySelector('.departamento-hidden').value = deptoSelect.value;

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
                opt.textContent = nombre.toUpperCase();
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
            const row = e.target.closest('.prestamo-item');
            const prodSel = row.querySelector('.producto');
            const val = parseInt(prodSel.value);

            if (val && productosUsados.has(val)) {
                productosUsados.delete(val);
            }

            row.remove();
            actualizarOpcionesProductos();
        }
    });

    container.addEventListener('change', e => {
        const row = e.target.closest('.prestamo-item');

        if (!row) return;

        const categoriaSel = row.querySelector('.categoria');
        const productoSel  = row.querySelector('.producto');

        if (e.target.classList.contains('categoria')) {
            const categoriaId = categoriaSel.value;
            const dptoId      = deptoSelect.value;

            productoSel.innerHTML = '<option value="">Seleccionar</option>';
            productoSel.disabled  = !categoriaId;

            if (categoriaId && dptoId) {
                const filtrados = productosGlobal.filter(p =>
                    p.departamento_id == dptoId &&
                    p.categoria_id    == categoriaId &&
                    !productosUsados.has(p.id)
                );

                filtrados.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = `${(p.codigo ?? '').toUpperCase()} | ${p.nombre.toUpperCase()} | Stock: ${p.stock}`;
                    productoSel.appendChild(opt);
                });
            }
        }

        if (e.target.classList.contains('producto')) {
            const nuevoVal = parseInt(productoSel.value);
            const prevVal  = productoSel.dataset.prevValue ? parseInt(productoSel.dataset.prevValue) : null;

            if (prevVal && productosUsados.has(prevVal)) {
                productosUsados.delete(prevVal);
            }

            if (nuevoVal) {
                productosUsados.add(nuevoVal);
                productoSel.dataset.prevValue = nuevoVal;
            } else {
                productoSel.removeAttribute('data-prev-value');
            }

            actualizarOpcionesProductos();
        }
    });

    formPrestamo.addEventListener('submit', e => {
        if (formPrestamo.dataset.enviando === '1') {
            e.preventDefault();
            return false;
        }

        if (!bodegaSelect.value || !deptoSelect.value) {
            e.preventDefault();
            Swal.fire('Atención', 'Debe seleccionar bodega y departamento antes de guardar.', 'warning');
            return false;
        }

        document.querySelectorAll('.departamento-hidden').forEach(inp => {
            inp.value = deptoSelect.value;
        });

        document.querySelectorAll('select:disabled').forEach(sel => {
            sel.disabled = false;
        });

        if (!formPrestamo.checkValidity()) {
            e.preventDefault();
            formPrestamo.reportValidity();
            return false;
        }

        formPrestamo.dataset.enviando = '1';

        if (btnRegistrarPrestamo) {
            btnRegistrarPrestamo.disabled = true;
            btnRegistrarPrestamo.style.pointerEvents = 'none';
            btnRegistrarPrestamo.innerHTML = `
                <span 
                    class="spinner-border spinner-border-sm me-2" 
                    role="status" 
                    aria-hidden="true"
                    style="width: 1rem; height: 1rem;">
                </span>
                Guardando...
            `;
        }

        return true;
    });

    @if(session('success'))
        Swal.fire({ 
            icon:'success', 
            title:'Éxito', 
            text:'{{ session('success') }}', 
            timer: 2200, 
            showConfirmButton:false 
        });
    @endif

    @if(session('error'))
        Swal.fire({ 
            icon:'error', 
            title:'Error', 
            text:'{{ session('error') }}' 
        });
    @endif
});
</script>

@endsection