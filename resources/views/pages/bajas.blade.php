@extends('welcome')

@section('title', 'Registro de Bajas')

@section('content')

<head>
    <link rel="stylesheet" href="{{ asset('assets/styleAggProduct.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dashboard.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<div class="container-fluid py-4">
    <h1 class="mb-4 text-secondary">📦 Registro de Bajas</h1>

    <div class="card mb-4 shadow border-0 rounded-4">
        <div class="card-header text-white fw-bold" style="background-color:#6c757d;">
            <i class="bi bi-archive me-2"></i>Registrar nueva baja
        </div>

        <div class="card-body">
            <form id="form-baja" method="POST" action="{{ route('bajas.store') }}">
                @csrf

                {{-- 🔹 Selección de Bodega --}}
                @if($bodega)
                    <div class="alert alert-info d-flex align-items-center mb-4 shadow-sm rounded-pill px-3 py-2">
                        <i class="bi bi-shop me-2 fs-5"></i>
                        <div><strong>Bodega asignada:</strong> {{ strtoupper($bodega->nombre) }}</div>
                        <input type="hidden" id="bodega-general" name="bodega_id" value="{{ $bodega->id }}">
                    </div>
                @else
                    <div class="alert alert-warning d-flex align-items-center mb-4 shadow-sm rounded-pill px-3 py-2">
                        <i class="bi bi-shield-lock me-2 fs-5"></i>
                        <div><strong>Modo administrador:</strong> Seleccione la bodega donde hará la baja.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Seleccione la bodega</label>
                        <select id="bodega-general" name="bodega_id" class="form-select w-50" required>
                            <option value="">Seleccionar bodega</option>
                            @foreach($bodegas as $b)
                                <option value="{{ $b->id }}">{{ strtoupper($b->nombre) }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- 🔹 Contenedor dinámico de filas --}}
                <div id="bajas-container">
                    <div class="baja-item row align-items-end mb-3">

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Departamento</label>
                            <select name="productos[0][departamento_id]" class="form-select departamento" required disabled>
                                <option value="">Seleccionar</option>
                            </select>
                        </div>

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

                        <div class="col-md-1 text-center">
                            <button type="button" class="btn btn-danger btn-remove mt-3">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                    </div>
                </div>

                <div class="text-end mb-3">
                    <button type="button" id="btn-add" class="btn btn-outline-secondary">
                        <i class="bi bi-plus-circle"></i> Agregar fila
                    </button>
                </div>

                {{-- 🔹 Motivo y Responsable --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Motivo</label>
                        <textarea name="motivo" class="form-control" required
                                  placeholder="Ej. Dañado, vencido, pérdida..."></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Responsable</label>
                        <input type="text" name="responsable" class="form-control" required
                               placeholder="Nombre del responsable">
                    </div>
                </div>

                <div class="text-end">
                    <button 
                        type="submit" 
                        id="btnRegistrarBaja"
                        class="btn text-white"
                        style="background-color:#dc3545;">
                        <i class="bi bi-check-circle me-1"></i> Registrar Baja
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- JS --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const container     = document.getElementById('bajas-container');
    const btnAdd        = document.getElementById('btn-add');
    const bodegaSelect  = document.getElementById('bodega-general');
    let productosGlobal = [];
    let productosUsados = [];
    let index           = 1;

    const formBaja = document.getElementById('form-baja');
    const btnRegistrarBaja = document.getElementById('btnRegistrarBaja');

    if (formBaja && btnRegistrarBaja) {
        formBaja.addEventListener('submit', function (e) {
            if (formBaja.dataset.enviando === '1') {
                e.preventDefault();
                return false;
            }

            if (!formBaja.checkValidity()) {
                formBaja.reportValidity();
                return false;
            }

            formBaja.dataset.enviando = '1';

            btnRegistrarBaja.disabled = true;
            btnRegistrarBaja.style.pointerEvents = 'none';

            btnRegistrarBaja.innerHTML = `
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
    }

    function cargarProductos(bodegaId) {
        if (!bodegaId) return;
        fetch(`/bodega/${bodegaId}/productos`)
            .then(res => res.json())
            .then(data => {
                productosGlobal = data;
                actualizarDepartamentos();
            })
            .catch(() => Swal.fire('Error', 'No se pudieron cargar los productos.', 'error'));
    }

    function actualizarDepartamentos() {
        const departamentos = [...new Set(productosGlobal.map(p => JSON.stringify({
            id: p.departamento_id,
            nombre: p.departamento_nombre
        })))]
        .map(j => JSON.parse(j));

        document.querySelectorAll('.departamento').forEach(sel => {
            const valorActual = sel.value;
            sel.innerHTML = '<option value="">Seleccionar</option>';
            departamentos.forEach(d => {
                if (d.id) {
                    sel.innerHTML += `<option value="${d.id}">${d.nombre.toUpperCase()}</option>`;
                }
            });
            sel.disabled = false;
            if (valorActual) sel.value = valorActual;
        });
    }

    // Jerarquía: Departamento -> Categoría -> Producto
    container.addEventListener('change', e => {
        const item = e.target.closest('.baja-item');
        if (!item) return;

        const depto     = item.querySelector('.departamento');
        const categoria = item.querySelector('.categoria');
        const producto  = item.querySelector('.producto');

        // Departamento
        if (e.target.classList.contains('departamento')) {
            categoria.innerHTML = '<option value="">Seleccionar</option>';
            producto.innerHTML  = '<option value="">Seleccionar</option>';
            categoria.disabled  = !depto.value;
            producto.disabled   = true;

            const categorias = [...new Set(
                productosGlobal
                    .filter(p => p.departamento_id == depto.value)
                    .map(p => JSON.stringify({ id: p.categoria_id, nombre: p.categoria_nombre }))
            )].map(j => JSON.parse(j));

            categorias.forEach(c => {
                categoria.innerHTML += `<option value="${c.id}">${c.nombre.toUpperCase()}</option>`;
            });
        }

        // Categoría
        if (e.target.classList.contains('categoria')) {
            producto.innerHTML = '<option value="">Seleccionar</option>';
            producto.disabled  = !categoria.value;

            const filtrados = productosGlobal.filter(p =>
                p.departamento_id == depto.value &&
                p.categoria_id    == categoria.value &&
                !productosUsados.includes(p.id)
            );

            if (filtrados.length === 0) {
                producto.innerHTML = '<option value="">— No hay productos disponibles —</option>';
                producto.disabled  = true;
            } else {
                filtrados.forEach(p => {
                    producto.innerHTML += `<option value="${p.id}">${p.nombre.toUpperCase()} | Stock: ${p.stock}</option>`;
                });
            }
        }

        // Producto
        if (e.target.classList.contains('producto')) {
            const idProd = parseInt(e.target.value);
            if (idProd && !productosUsados.includes(idProd)) {
                productosUsados.push(idProd);
            }
        }
    });

    // Agregar fila
    btnAdd.addEventListener('click', () => {
        const clone = container.firstElementChild.cloneNode(true);

        clone.querySelectorAll('input,select').forEach(el => {
            const old = el.getAttribute('name');
            if (old) {
                el.setAttribute('name', old.replace(/\[\d+\]/, `[${index}]`));
            }
            if (el.tagName === 'INPUT') el.value = '';
        });

        const deptoOriginal = container.querySelector('.departamento');
        clone.querySelector('.departamento').innerHTML = deptoOriginal.innerHTML;

        clone.querySelector('.categoria').innerHTML = '<option value="">Seleccionar</option>';
        clone.querySelector('.producto').innerHTML  = '<option value="">Seleccionar</option>';
        clone.querySelector('.categoria').disabled  = true;
        clone.querySelector('.producto').disabled   = true;

        container.appendChild(clone);
        index++;
    });

    // Eliminar fila
    container.addEventListener('click', e => {
        const btn = e.target.closest('.btn-remove');
        if (btn && container.children.length > 1) {
            const productoSelect = btn.closest('.baja-item').querySelector('.producto');
            const idProd = parseInt(productoSelect.value);
            if (idProd) {
                productosUsados = productosUsados.filter(id => id !== idProd);
            }
            btn.closest('.baja-item').remove();
        }
    });

    // Cambio de bodega
    if (bodegaSelect && bodegaSelect.value) {
        cargarProductos(bodegaSelect.value);
    }

    bodegaSelect?.addEventListener('change', e => {
        const nuevaBodega = e.target.value;

        productosUsados = [];
        productosGlobal = [];
        container.innerHTML = '';

        const nuevaFila = document.createElement('div');
        nuevaFila.className = 'baja-item row align-items-end mb-3';
        nuevaFila.innerHTML = `
            <div class="col-md-3">
                <label class="form-label fw-semibold">Departamento</label>
                <select name="productos[0][departamento_id]" class="form-select departamento" required disabled>
                    <option value="">Seleccionar</option>
                </select>
            </div>

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

            <div class="col-md-1 text-center">
                <button type="button" class="btn btn-danger btn-remove mt-3">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        `;
        container.appendChild(nuevaFila);
        index = 1;

        if (nuevaBodega) {
            cargarProductos(nuevaBodega);
        }
    });

    // SweetAlerts flash
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: '{{ session("success") }}',
            timer: 1800,
            showConfirmButton: false
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session("error") }}'
        });
    @endif
});
</script>

@endsection
