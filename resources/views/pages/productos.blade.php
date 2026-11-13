@extends('welcome')

@section('title', 'Lista de Productos')

@section('content')
    <div class="container-fluid py-4">
        <h1 class="page-title mt-4">📋 Lista de Artículos</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('panel') }}">Panel</a></li>
            <li class="breadcrumb-item active">Artículos</li>
        </ol>

        {{-- 🔹 Modo --}}
        @if ($bodega)
            <div class="alert alert-info d-flex align-items-center mb-4 shadow-sm rounded-pill px-3 py-2">
                <i class="bi bi-shop me-2 fs-5"></i>
                <div><strong>Bodega asignada:</strong> {{ $bodega->nombre }}</div>
            </div>

            {{-- 🔹 Botón consulta global --}}
            <div class="d-flex justify-content-end mb-3">
                <button id="btnConsultaGlobal" class="btn btn-outline-primary rounded-pill px-4">
                    <i class="bi bi-eye"></i> Ver otras bodegas
                </button>
            </div>
        @else
            <div class="alert alert-warning d-flex align-items-center mb-4 shadow-sm rounded-pill px-3 py-2">
                <i class="bi bi-shield-lock me-2 fs-5"></i>
                <div><strong>Modo administrador:</strong> Puede gestionar productos en todas las bodegas.</div>
            </div>
        @endif

        <!-- 🔹 Botón para abrir modal agregar -->
        <div class="view-actions">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregarProducto">
                <i class="bi bi-plus-circle"></i> Añadir Artículo
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalImportarExcel">
                <i class="bi bi-upload"></i> Importar Excel
            </button>
            <button id="btnExportarExcel" class="btn btn-success rounded-pill px-4">
                <i class="bi bi-file-earmark-excel"></i> Exportar Excel
            </button>
        </div>

        {{-- 🔹 Modales --}}
        @include('pages.partials.modal-agregar-producto')
        @include('pages.partials.modal-editar-producto')
        @include('pages.partials.modal-consulta-producto')
        @include('pages.partials.modal-importar-excel')

        <!-- 🔹 Tabla de Productos -->
        <div class="card shadow-sm">
            <div class="card-header text-white" style="background-color: #198754;">
                <h5 class="mb-0">📦 Detalle de Artículos</h5>
            </div>
            <div class="card-body">
                {{-- Buscador y filtro --}}
                <form id="busquedaForm" method="GET" action="{{ route('productos') }}"
                    class="mb-4 p-3 shadow-sm rounded-4 border bg-light">
                    <div class="row align-items-center g-3">
                        
                        {{-- Buscador --}}
                        <div class="col-lg-4 col-md-12 d-flex align-items-center">
                            <div class="input-group">
                                <span class="input-group-text bg-success text-white border-0 rounded-start-pill px-3">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" id="buscarInput" name="buscar"
                                    class="form-control form-control-lg border-0 rounded-end-pill"
                                    placeholder="Buscar artículo..." value="{{ request('buscar') }}">
                            </div>
                        </div>

                        {{-- Botones --}}
                        <div class="col-lg-2 col-md-12 text-md-start text-center">
                            <div class="d-flex flex-wrap justify-content-md-start justify-content-center gap-2">
                                <button type="submit" class="btn btn-success rounded-pill px-3 py-2 fw-semibold shadow-sm">
                                    <i class="bi bi-search me-1"></i> Buscar
                                </button>
                                <button type="button" id="btnLimpiarBusqueda"
                                    class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold shadow-sm">
                                    <i class="bi bi-x-circle me-1"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Filtro Bodega (Solo si no tiene bodega asignada) --}}
                        @if (!$bodega)
                            <div class="col-lg-3 col-md-6 text-md-end text-center">
                                <label for="filtroBodega" class="form-label fw-semibold text-success mb-1">
                                    Filtrar por bodega
                                </label>
                                <select id="filtroBodega" class="form-select rounded-pill px-4 border-success shadow-sm"
                                    style="max-width: 230px; display:inline-block;">
                                    <option value="">Todas las bodegas</option>
                                    @foreach ($bodegas as $bod)
                                        <option value="{{ $bod->id }}">{{ strtoupper($bod->nombre) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Filtro Departamento (Para todos) --}}
                        <div class="col-lg-3 col-md-6 text-md-end text-center mt-3 mt-md-0">
                            <label for="filtroDepartamento" class="form-label fw-semibold text-success mb-1">
                                Filtrar por departamento
                            </label>
                            <select id="filtroDepartamento" class="form-select rounded-pill px-4 border-success shadow-sm"
                                style="max-width: 230px; display:inline-block;">
                                <option value="">Todos los departamentos</option>
                                @foreach ($departamentos as $dep)
                                    <option value="{{ $dep->id }}">{{ strtoupper($dep->nombre) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>

                {{-- Tabla --}}
                @if ($productos->isEmpty())
                    <div class="alert alert-info text-center rounded-3 py-2 mt-4">
                        <i class="bi bi-info-circle me-2"></i> No hay artículos registrados.
                    </div>
                @else
                    <div id="loader" class="text-center my-4" style="display:none;">
                        <div class="spinner-border text-success" role="status"></div>
                        <p class="mt-2 text-success fw-semibold">Cargando artículos...</p>
                    </div>

                    <div class="table-responsive">
                        <table class="custom-table text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>UM</th>
                                    <th>Stock</th>
                                    <th>Categoría</th>
                                    <th>Departamento</th>
                                    <th>Bodega</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaProductos">
                                @foreach ($productos as $producto)
                                    <tr>
                                        <td>{{ $producto->codigo }}</td>
                                        <td>{{ $producto->nombre }}</td>
                                        <td>{{ $producto->unidades->nombre ?? '—' }}</td>
                                        <td>{{ $producto->stock }}</td>
                                        <td>{{ $producto->categoria->nombre ?? '—' }}</td>
                                        <td>{{ $producto->departamento->nombre ?? '—' }}</td>
                                        <td>{{ $producto->bodega->nombre ?? '—' }}</td>
                                        <td>{{ $producto->tipoProducto->nombre ?? '—' }}</td>
                                        <td>
                                            <span class="estado-producto {{ $producto->estado ? 'estado-activo' : 'estado-inactivo' }}">
                                                {{ $producto->estado ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <button class="btn btn-warning btn-sm btnEditar"
                                                    data-id="{{ $producto->id }}"
                                                    data-update-url="{{ route('productos.update', $producto->id) }}"
                                                    data-codigo="{{ $producto->codigo }}"
                                                    data-nombre="{{ $producto->nombre }}"
                                                    data-descripcion="{{ $producto->descripcion ?? '' }}"
                                                    data-stock="{{ $producto->stock }}"
                                                    data-categoria-id="{{ $producto->categoria_id }}"
                                                    data-unidades-id="{{ $producto->unidades_id }}"
                                                    data-departamento-id="{{ $producto->departamento_id }}"
                                                    data-bodega-id="{{ $producto->bodega_id }}"
                                                    data-tipo-id="{{ $producto->tipo_producto_id }}"
                                                    data-bs-toggle="modal" data-bs-target="#modalEditarProducto">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>

                                                <form class="form-toggle d-inline" method="POST"
                                                    action="{{ $producto->estado ? route('productos.desactivar', $producto->id) : route('productos.activar', $producto->id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit"
                                                        class="btn btn-sm {{ $producto->estado ? 'btn-danger' : 'btn-success' }}">
                                                        <i class="bi bi-toggle-{{ $producto->estado ? 'off' : 'on' }}"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div id="paginacion-container" class="d-flex justify-content-center mt-3">
                        {{ $productos->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection


<!-- // ========================================================================
// 🔹 JAVASCRIPT
// ======================================================================== -->

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- 🔹 Variables PHP accesibles al script externo --}}
    <script>
        window.LaravelMessages = {
            success: @json(session('success')),
            error: @json(session('error')),
            errors: @json($errors->all())
        };
        const BASE_PRODUCTOS_UPDATE = "{{ url('productos') }}";
    </script>

    {{-- 🔹 Script principal de productos --}}
    <script>
        // ========================================================================
        // 🔹 BUSCADOR PRINCIPAL: BUSCAR Y LIMPIAR
        // ========================================================================
        document.addEventListener('DOMContentLoaded', () => {
            const formBusqueda = document.getElementById('busquedaForm');
            const buscarInput = document.getElementById('buscarInput');
            const btnLimpiarBusqueda = document.getElementById('btnLimpiarBusqueda');

            if (formBusqueda && buscarInput) { 
                formBusqueda.addEventListener('submit', e => {
                    const termino = buscarInput.value.trim();
                    buscarInput.value = termino;

                    if (termino === '') {
                        e.preventDefault();
                        window.location.href = formBusqueda.action;
                    }
                });
            }

            if (btnLimpiarBusqueda && formBusqueda && buscarInput) {
                btnLimpiarBusqueda.addEventListener('click', () => {
                    buscarInput.value = '';
                    const url = new URL(formBusqueda.action, window.location.origin);
                    window.location.href = url.toString();
                });
            }
        });

        // ========================================================================
        // 🔹 MODAL DE CONSULTA GLOBAL DE OTRAS BODEGAS
        // ========================================================================
        document.addEventListener('DOMContentLoaded', () => {
            const btnConsulta = document.getElementById('btnConsultaGlobal');
            const modalConsulta = document.getElementById('modalConsultaGlobal');
            const tablaConsulta = document.querySelector('#tablaConsultaGlobal tbody');
            const consultaLoader = document.getElementById('consultaLoader');
            const filtroBodega = document.getElementById('filtroBodegaConsulta');
            const inputBuscar = document.getElementById('buscarConsulta');
            const btnBuscar = document.getElementById('btnBuscarConsulta');
            const paginacionDiv = document.getElementById('consultaPaginacion');
            let paginaActual = 1;

            async function cargarConsulta(page = 1) {
                const buscar = inputBuscar.value.trim();
                const bodega = filtroBodega.value;
                paginaActual = page;

                consultaLoader.style.display = 'block';
                tablaConsulta.innerHTML = '';
                paginacionDiv.innerHTML = '';

                try {
                    const params = new URLSearchParams({ buscar, bodega, page });
                    const response = await fetch(`/productos/otras-bodegas?${params}`);
                    const data = await response.json();

                    consultaLoader.style.display = 'none';
                    const productos = data.data ?? [];

                    if (productos.length === 0) {
                        tablaConsulta.innerHTML = `<tr><td colspan="8" class="text-muted">No hay productos encontrados.</td></tr>`;
                        return;
                    }

                    tablaConsulta.innerHTML = productos.map(p => `
                        <tr>
                            <td>${p.codigo ?? ''}</td>
                            <td>${p.nombre ?? ''}</td>
                            <td>${p.descripcion ?? ''}</td>
                            <td>${p.bodega?.nombre ?? p.bodega_nombre ?? '—'}</td>
                            <td>${p.stock ?? 0}</td>
                            <td>${p.unidades?.nombre ?? p.unidades_nombre ?? '—'}</td>
                            <td>${p.categoria?.nombre ?? p.categoria_nombre ?? '—'}</td>
                            <td>${p.departamento?.nombre ?? p.departamento_nombre ?? '—'}</td>
                        </tr>
                    `).join('');

                    if (data.last_page > 1 && Array.isArray(data.links)) {
                        const linksHtml = data.links
                            .filter(l => l.url)
                            .map(l => `
                                <li class="page-item ${l.active ? 'active' : ''}">
                                    <a class="page-link" href="#" data-page="${new URL(l.url).searchParams.get('page')}">
                                        ${l.label.replace('&laquo;', '‹').replace('&raquo;', '›')}
                                    </a>
                                </li>
                            `).join('');

                        paginacionDiv.innerHTML = `<nav><ul class="pagination justify-content-center mb-0">${linksHtml}</ul></nav>`;

                        paginacionDiv.querySelectorAll('.page-link').forEach(link => {
                            link.addEventListener('click', e => {
                                e.preventDefault();
                                const page = link.getAttribute('data-page');
                                if (page) cargarConsulta(page);
                            });
                        });
                    }
                } catch (error) {
                    console.error('Error al obtener los datos:', error);
                    consultaLoader.style.display = 'none';
                    tablaConsulta.innerHTML = `<tr><td colspan="8" class="text-danger">Error al obtener los datos.</td></tr>`;
                }
            }

            if (btnConsulta) {
                btnConsulta.addEventListener('click', () => {
                    const modal = new bootstrap.Modal(modalConsulta);
                    modal.show();
                    cargarConsulta(1);
                });
            }

            btnBuscar.addEventListener('click', () => cargarConsulta(1));
            filtroBodega.addEventListener('change', () => cargarConsulta(1));
            inputBuscar.addEventListener('keypress', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    cargarConsulta(1);
                }
            });
        });

        // ========================================================================
        // 🔹 CAPTURA DE PÁGINA ACTUAL PARA MODAL EDITAR
        // ========================================================================
        document.addEventListener('DOMContentLoaded', () => {
            const formEditar = document.getElementById('formEditarProducto');
            if (!formEditar) return;
            const pageInput = document.getElementById('editPage') || formEditar.querySelector('input[name="page"]');
            const urlParams = new URLSearchParams(window.location.search);
            const currentPage = urlParams.get('page') || 1;
            if (pageInput) pageInput.value = currentPage;
        });

        // ========================================================================
        // 🔹 FUNCIONES GLOBALES PARA FILTROS Y PAGINACIÓN
        // ========================================================================
        
        window.activarDesactivarAjax = function() {
            document.querySelectorAll('.form-toggle').forEach(form => {
                if (form.dataset.ajaxReady === 'true') return;
                form.dataset.ajaxReady = 'true';

                form.addEventListener('submit', async e => {
                    e.preventDefault();

                    const confirm = await Swal.fire({
                        title: '¿Estás seguro?',
                        text: 'Este cambio modificará el estado del producto.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, continuar',
                        cancelButtonText: 'Cancelar'
                    });

                    if (!confirm.isConfirmed) return;

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: new FormData(form),
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Hecho',
                                text: result.message ?? 'El estado se actualizó correctamente.',
                                showConfirmButton: false,
                                timer: 1200
                            });

                            const fila = form.closest('tr');
                            const estadoCell = fila.querySelector('.estado-producto');
                            const boton = form.querySelector('button');
                            const botonIcono = boton.querySelector('i');

                            if (!estadoCell) return;

                            if (result.estado === 'inactivo') {
                                estadoCell.textContent = 'Inactivo';
                                estadoCell.className = 'estado-producto estado-inactivo';
                                form.action = form.action.replace('desactivar', 'activar');
                                boton.className = 'btn btn-sm btn-success';
                                botonIcono.className = 'bi bi-toggle-on';
                            } else {
                                estadoCell.textContent = 'Activo';
                                estadoCell.className = 'estado-producto estado-activo';
                                form.action = form.action.replace('activar', 'desactivar');
                                boton.className = 'btn btn-sm btn-danger';
                                botonIcono.className = 'bi bi-toggle-off';
                            }
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: result?.message ?? 'No se pudo actualizar el estado.' });
                        }
                    } catch (err) {
                        console.error('❌ Error:', err);
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Error en la conexión con el servidor.' });
                    }
                });
            });
        };

        // Función global para cargar productos (Bodega o Departamento)
        window.cargarProductos = async function(page = 1) {
            const tabla = document.getElementById('tablaProductos');
            const loader = document.getElementById('loader');
            const paginacionContainer = document.getElementById('paginacion-container');
            const selectBodega = document.getElementById('filtroBodega');
            const selectDepartamento = document.getElementById('filtroDepartamento');

            const bodegaId = selectBodega ? selectBodega.value : '';
            const departamentoId = selectDepartamento ? selectDepartamento.value : '';

            let url = `/productos/todos?page=${page}`;
            
            // Prioridad: Departamento > Bodega
            if (departamentoId) {
                url = `/productos/departamento/${departamentoId}?page=${page}`;
            } else if (bodegaId) {
                url = `/productos/bodega/${bodegaId}?page=${page}`;
            }

            if (loader) loader.style.display = 'block';
            if (tabla) tabla.innerHTML = '';

            try {
                const response = await fetch(url);
                const data = await response.json();
                const productos = data.data ?? [];

                if (productos.length === 0) {
                    tabla.innerHTML = `<tr><td colspan="10" class="text-center text-muted">No hay productos registrados.</td></tr>`;
                } else {
                    tabla.innerHTML = productos.map(p => `
                        <tr>
                            <td>${p.codigo ?? ''}</td>
                            <td>${p.nombre ?? ''}</td>
                            <td>${p.unidades?.nombre ?? p.unidades_nombre ?? '—'}</td>
                            <td class="text-center">${p.stock ?? 0}</td>
                            <td>${p.categoria?.nombre ?? p.categoria_nombre ?? '—'}</td>
                            <td>${p.departamento?.nombre ?? p.departamento_nombre ?? '—'}</td>
                            <td>${p.bodega?.nombre ?? p.bodega_nombre ?? '—'}</td>
                            <td>${p.tipo_producto?.nombre ?? p.tipo_producto_nombre ?? '—'}</td>
                            <td class="text-center">
                                <span class="estado-producto ${p.estado == 1 ? 'estado-activo' : 'estado-inactivo'}">
                                    ${p.estado == 1 ? 'Activo' : 'Inactivo'}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-warning btn-sm btnEditar"
                                        data-id="${p.id}"
                                        data-update-url="${BASE_PRODUCTOS_UPDATE}/${p.id}"
                                        data-codigo="${p.codigo ?? ''}"
                                        data-nombre="${p.nombre ?? ''}"
                                        data-descripcion="${p.descripcion ? String(p.descripcion).replace(/"/g,'&quot;') : ''}"
                                        data-stock="${p.stock ?? 0}"
                                        data-categoria-id="${p.categoria_id ?? (p.categoria?.id ?? '')}"
                                        data-unidades-id="${p.unidades_id ?? (p.unidades?.id ?? '')}"
                                        data-departamento-id="${p.departamento_id ?? (p.departamento?.id ?? '')}"
                                        data-bodega-id="${p.bodega_id ?? (p.bodega?.id ?? '')}"
                                        data-tipo-id="${p.tipo_producto_id ?? (p.tipo_producto?.id ?? '')}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditarProducto">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    <form class="form-toggle" method="POST" action="/productos/${p.id}/${p.estado == 1 ? 'desactivar' : 'activar'}">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm ${p.estado == 1 ? 'btn-danger' : 'btn-success'}">
                                            <i class="bi bi-toggle-${p.estado == 1 ? 'off' : 'on'}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    `).join('');
                }

                window.activarDesactivarAjax();

                if (paginacionContainer) {
                    paginacionContainer.innerHTML = '';
                    if (data.last_page > 1 && Array.isArray(data.links)) {
                        const linksHtml = data.links
                            .filter(l => l.url !== null)
                            .map(l => `
                                <li class="page-item ${l.active ? 'active' : ''}">
                                    <a class="page-link" href="#" data-page="${new URL(l.url).searchParams.get('page')}">
                                        ${l.label.replace('&laquo;', '‹').replace('&raquo;', '›')}
                                    </a>
                                </li>
                            `).join('');

                        paginacionContainer.innerHTML = `<nav><ul class="pagination justify-content-center mb-0">${linksHtml}</ul></nav>`;

                        paginacionContainer.querySelectorAll('.page-link').forEach(link => {
                            link.addEventListener('click', e => {
                                e.preventDefault();
                                const page = link.getAttribute('data-page');
                                if (page) window.cargarProductos(page);
                            });
                        });
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                tabla.innerHTML = `<tr><td colspan="10" class="text-center text-danger">Error al obtener los datos.</td></tr>`;
            } finally {
                if (loader) loader.style.display = 'none';
            }
        };

        // ========================================================================
        // 🔹 INICIALIZACIÓN DE FILTROS Y CARGA
        // ========================================================================
        document.addEventListener('DOMContentLoaded', () => {
            const selectBodega = document.getElementById('filtroBodega');
            const selectDepartamento = document.getElementById('filtroDepartamento');
            const buscarInput = document.getElementById('buscarInput');

            const paramsActuales = new URLSearchParams(window.location.search);
            const busquedaActiva = (paramsActuales.get('buscar') || '').trim();

            // Restaurar estados guardados
            if (selectBodega) {
                const guardada = localStorage.getItem('bodegaSeleccionada');
                if (guardada) selectBodega.value = guardada;
            }
            if (selectDepartamento) {
                const guardada = localStorage.getItem('departamentoSeleccionado');
                if (guardada) selectDepartamento.value = guardada;
            }

            // EVENTO: Cambio en Bodega
            if (selectBodega) {
                selectBodega.addEventListener('change', () => {
                    const bodegaId = selectBodega.value;
                    localStorage.setItem('bodegaSeleccionada', bodegaId);

                    // Limpiar departamento si se elige bodega
                    if (bodegaId && selectDepartamento) {
                        selectDepartamento.value = '';
                        localStorage.removeItem('departamentoSeleccionado');
                    }

                    if (buscarInput) buscarInput.value = '';
                    const urlSinBusqueda = new URL(window.location.href);
                    urlSinBusqueda.searchParams.delete('buscar');
                    urlSinBusqueda.searchParams.delete('page');
                    window.history.replaceState({}, '', urlSinBusqueda);

                    window.cargarProductos(1);
                });
            }

            // EVENTO: Cambio en Departamento
            if (selectDepartamento) {
                selectDepartamento.addEventListener('change', () => {
                    const departamentoId = selectDepartamento.value;
                    localStorage.setItem('departamentoSeleccionado', departamentoId);

                    // Limpiar bodega si se elige departamento
                    if (departamentoId && selectBodega) {
                        selectBodega.value = '';
                        localStorage.removeItem('bodegaSeleccionada');
                    }

                    if (buscarInput) buscarInput.value = '';
                    const urlSinBusqueda = new URL(window.location.href);
                    urlSinBusqueda.searchParams.delete('buscar');
                    urlSinBusqueda.searchParams.delete('page');
                    window.history.replaceState({}, '', urlSinBusqueda);

                    window.cargarProductos(1);
                });
            }

            // Carga inicial (solo si no hay búsqueda activa por URL)
            if (!busquedaActiva) {
                window.cargarProductos(1);
            }
        });

        // ========================================================================
        // 🔹 RELLENAR MODAL EDITAR PRODUCTO
        // ========================================================================
        document.addEventListener('click', e => {
            const btn = e.target.closest('.btnEditar');
            if (!btn) return;

            document.getElementById('editCodigoView').value = btn.getAttribute('data-codigo') ?? '';
            document.getElementById('editNombre').value = btn.getAttribute('data-nombre') ?? '';
            document.getElementById('editDescripcion').value = btn.getAttribute('data-descripcion') ?? '';
            document.getElementById('editStock').value = btn.getAttribute('data-stock') ?? 0;

            const setVal = (id, val) => {
                const el = document.getElementById(id);
                if (el) {
                    el.value = val ?? '';
                    el.dispatchEvent(new Event('change'));
                }
            };

            setVal('editCategoria', btn.getAttribute('data-categoria-id'));
            setVal('editUnidad', btn.getAttribute('data-unidades-id'));
            setVal('editDepartamento', btn.getAttribute('data-departamento-id'));
            setVal('editTipo', btn.getAttribute('data-tipo-id'));
            setVal('editBodega', btn.getAttribute('data-bodega-id'));

            const urlParams = new URLSearchParams(window.location.search);
            const currentPage = urlParams.get('page') || 1;
            const formAction = `${btn.getAttribute('data-update-url')}?page=${currentPage}`;
            document.getElementById('formEditarProducto').action = formAction;
        });

        // ========================================================================
        // 🔹 MENSAJES SWEETALERT DE SESIÓN Y EXPORTAR EXCEL
        // ========================================================================
        window.addEventListener('load', () => {
            const { success, error, errors } = window.LaravelMessages || {};

            if (success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: success,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    // Refrescar tabla usando la función global
                    if (typeof window.cargarProductos === 'function') {
                        window.cargarProductos(1);
                    } else {
                        location.reload();
                    }
                });
            } else if (error) {
                Swal.fire({ icon: 'error', title: 'Error', text: error });
            } else if (errors && errors.length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Revisa los datos',
                    html: '<ul style="text-align:left;margin:0;padding-left:18px">' + errors.map(e => `<li>${e}</li>`).join('') + '</ul>'
                });
            }
        });

        // ========================================================================
        // 🔹 EXPORTAR EXCEL (SweetAlert confirmación)
        // ========================================================================
        document.addEventListener('DOMContentLoaded', () => {
            const btnExport = document.getElementById('btnExportarExcel');
            if (!btnExport) return;

            btnExport.addEventListener('click', () => {
                const inputBuscar = document.getElementById('buscarInput');
                const filtroBodega = document.getElementById('filtroBodega');
                const buscar = inputBuscar ? inputBuscar.value.trim() : '';
                const bodega = filtroBodega ? filtroBodega.value : '';

                const url = `/productos/export?buscar=${encodeURIComponent(buscar)}&bodega=${encodeURIComponent(bodega)}`;
                window.open(url, '_blank');

                Swal.fire({
                    icon: 'success',
                    title: 'Exportación iniciada',
                    text: 'El archivo Excel se está descargando.',
                    showConfirmButton: false,
                    timer: 2500
                });
            });
        });
    </script>
@endpush