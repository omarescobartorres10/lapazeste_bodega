@extends('layouts.public')

@section('title', 'Solicitud de Productos')

@section('content')

@php
    $sol  = $solicitante ?? null;
    $tipo = $tipoMovimiento ?? '';

    $tituloTipo = match($tipo) {
        'TRASLADO' => 'TRASLADO DE PRODUCTOS',
        'SALIDA'   => 'SALIDA DE PRODUCTOS',
        'PRESTAMO' => 'PRÉSTAMO DE PRODUCTOS',
        default    => 'SOLICITUD DE PRODUCTOS',
    };
@endphp

<div class="container py-4" style="max-width:1100px;">

    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="mb-1">
                <i class="bi bi-pencil-square me-2"></i>
                {{ $tituloTipo }}
            </h1>

            @if(!empty($tipo))
                <span class="badge bg-info text-dark">
                    Tipo: {{ $tipo }}
                </span>
            @endif
        </div>

        <div class="col-md-6">
            <div class="text-end mt-3">
                <a href="{{ route('solicitud.consultar') }}" class="btn btn-warning px-4 me-2">
                    <i class="bi bi-search"></i> Consultar Solicitud
                </a>

                <a href="{{ route('solicitud.tipo') }}" class="btn btn-outline-primary me-2">
                    <i class="bi bi-arrow-left-right"></i> Cambiar tipo
                </a>

                <a href="{{ route('solicitud.logout') }}" class="btn btn-outline-danger">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
            </div>
        </div>
    </div>

    {{-- ALERTAS --}}
    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: '{{ session('success') }}',
                confirmButtonColor: '#0d6efd'
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('error') }}',
                confirmButtonColor: '#dc3545'
            });
        </script>
    @endif

    <div class="card shadow-lg rounded-4 border-0">
        <div class="card-header text-white fw-bold" style="background:#0d6efd;">
            <i class="bi bi-card-checklist me-2"></i> Formulario de solicitud
        </div>

        <div class="card-body p-4">
            <form id="form-solicitud" method="POST" action="{{ route('solicitud.enviar') }}">
                @csrf

                {{-- tipo al backend --}}
                <input type="hidden" name="tipo_movimiento" value="{{ $tipo }}">

                {{-- DUI oculto --}}
                <input type="hidden" name="dui" value="{{ $sol->dui ?? '' }}">

                <h5 class="mb-3"><i class="bi bi-person-circle me-2"></i>Datos del solicitante</h5>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nombre completo</label>
                        <input type="text" name="solicitante" class="form-control"
                               value="{{ $sol->nombre ?? '' }}" readonly required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Teléfono</label>
                        <input type="text" name="telefono" class="form-control"
                               value="{{ $sol->telefono ?? '' }}"
                               placeholder="0000-0000">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Correo</label>
                        <input type="email" name="correo" class="form-control"
                               value="{{ $sol->correo ?? '' }}"
                               placeholder="example@lapazeste.gob.sv">
                    </div>
                </div>

                {{-- FECHAS + BODEGA ORIGEN --}}
                <div class="row mb-3">

                    {{-- Fecha requerida: para SALIDA/TRASLADO --}}
                    <div class="col-md-6" id="wrap-fecha-requerida">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-calendar-event me-1 text-primary"></i>
                            Fecha requerida
                        </label>
                        <input type="date" id="fecha_requerida" name="fecha_requerida" class="form-control"
                               min="{{ date('Y-m-d') }}">
                    </div>

                    {{-- Bodega origen --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-shop me-1 text-primary"></i>
                            Seleccione la bodega (origen)
                        </label>
                        <select id="bodega-publica" name="bodega_id" class="form-select" required>
                            <option value="">Seleccionar bodega</option>
                            @foreach($bodegas as $bod)
                                <option value="{{ $bod->id }}">{{ strtoupper($bod->nombre) }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                {{-- SOLO PRESTAMO: usa fecha_requerida como "fecha préstamo" + fecha_devolucion --}}
                <div id="wrap-prestamo-fechas" class="row mb-4" style="display:none;">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-calendar-check me-1 text-primary"></i>
                            Fecha de préstamo (salida)
                        </label>

                        {{-- IMPORTANTE: el backend usa fecha_requerida --}}
                        <input type="date" id="fecha_prestamo" class="form-control"
                               min="{{ date('Y-m-d') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-calendar-x me-1 text-danger"></i>
                            Fecha de devolución
                        </label>
                        <input type="date" id="fecha_devolucion" name="fecha_devolucion" class="form-control"
                               min="{{ date('Y-m-d') }}">
                    </div>
                </div>

                {{-- BODEGA DESTINO (SOLO TRASLADO) --}}
                <div id="wrap-bodega-destino" class="mb-4" style="display:none;">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-truck me-1 text-success"></i>
                        Bodega destino
                    </label>
                    <select id="bodega-destino" name="bodega_destino_id" class="form-select">
                        <option value="">Seleccione bodega destino</option>
                    </select>
                </div>

                {{-- DEPARTAMENTO SOLO LECTURA --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-diagram-3 me-1 text-primary"></i>
                        Departamento del solicitante
                    </label>

                    <input type="text" class="form-control"
                           value="{{ $sol->departamento->nombre ?? '' }}" readonly>

                    <input type="hidden" name="departamento_id" value="{{ $sol->departamento_id }}">
                </div>

                <h5 class="mb-3"><i class="bi bi-box-seam me-2"></i>Productos solicitados</h5>

                <div id="productos-container" class="border rounded-3 p-3 bg-light mb-3">
                    <div class="producto-item row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Producto</label>
                            <select name="productos[0][producto_id]" class="form-select producto" disabled>
                                <option value="">Seleccione una bodega primero</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Cantidad</label>
                            <input type="number" name="productos[0][cantidad]" min="1" class="form-control" required>
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

                <div class="text-end mb-4">
                    <button type="button" id="btn-add" class="btn btn-outline-primary">
                        <i class="bi bi-plus-circle me-1"></i> Agregar producto
                    </button>
                </div>

                <div class="text-end">
                    <button type="button" id="btn-preview" class="btn btn-primary px-4">
                        <i class="bi bi-eye-fill me-1"></i> Vista previa
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- MODAL PREVIEW --}}
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Previsualización de solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="preview-content"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" id="btn-enviar" class="btn btn-success">
                    <i class="bi bi-printer"></i> Generar PDF y Enviar
                </button>
            </div>

        </div>
    </div>
</div>
@endsection


@push('js')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const tipoMovimiento = @json($tipo);

    const telefonoInput = document.querySelector("input[name='telefono']");
    telefonoInput.addEventListener("input", function () {
        let val = this.value.replace(/\D/g, "").substring(0, 8);
        this.value = val.length > 4
            ? val.substring(0, 4) + "-" + val.substring(4)
            : val;
    });

    const bodegaSelect   = document.getElementById('bodega-publica');
    const container      = document.getElementById('productos-container');
    const btnAdd         = document.getElementById('btn-add');
    const btnPreview     = document.getElementById('btn-preview');
    const btnEnviar      = document.getElementById('btn-enviar');

    const wrapDestino    = document.getElementById('wrap-bodega-destino');
    const destinoSelect  = document.getElementById('bodega-destino');

    const wrapPrestamoFechas   = document.getElementById('wrap-prestamo-fechas');
    const fechaPrestamoInput   = document.getElementById('fecha_prestamo'); // UI
    const fechaDevolucionInput = document.getElementById('fecha_devolucion'); // backend
    const fechaRequeridaInput  = document.getElementById('fecha_requerida'); // backend

    const wrapFechaRequerida = document.getElementById('wrap-fecha-requerida');

    const departamentoId = {{ $sol->departamento_id }};
    let productosGlobal = [];
    let index = 1;

    function toggleDestinoUI() {
        if (!wrapDestino || !destinoSelect) return;

        if (tipoMovimiento === 'TRASLADO') {
            wrapDestino.style.display = 'block';
        } else {
            wrapDestino.style.display = 'none';
            destinoSelect.innerHTML = `<option value="">Seleccione bodega destino</option>`;
        }
    }

    function togglePrestamoUI() {
        if (!wrapPrestamoFechas) return;

        if (tipoMovimiento === 'PRESTAMO') {
            wrapPrestamoFechas.style.display = 'flex';
            if (wrapFechaRequerida) wrapFechaRequerida.style.display = 'none';

            // IMPORTANTE: en PRESTAMO, fecha_requerida será igual a fecha_prestamo
            if (fechaRequeridaInput) fechaRequeridaInput.value = '';
        } else {
            wrapPrestamoFechas.style.display = 'none';
            if (wrapFechaRequerida) wrapFechaRequerida.style.display = 'block';

            if (fechaPrestamoInput) fechaPrestamoInput.value = '';
            if (fechaDevolucionInput) fechaDevolucionInput.value = '';
        }
    }

    toggleDestinoUI();
    togglePrestamoUI();

    // Mantener fecha_requerida sincronizada con fecha_prestamo para PRESTAMO
    if (tipoMovimiento === 'PRESTAMO' && fechaPrestamoInput && fechaRequeridaInput) {
        fechaPrestamoInput.addEventListener('change', () => {
            fechaRequeridaInput.value = fechaPrestamoInput.value; // backend
        });
    }

    function limpiarSelectsProductos() {
        document.querySelectorAll('.producto').forEach(select => {
            select.innerHTML = `<option value="">Seleccione una bodega primero</option>`;
            select.disabled = true;
        });
    }

    function actualizarSelectsProductos() {
        document.querySelectorAll('.producto').forEach(select => {
            const prevValue = select.value;

            select.innerHTML = `<option value="">Seleccionar producto</option>`;
            productosGlobal.forEach(p => {
                select.innerHTML += `<option value="${p.id}">${p.nombre.toUpperCase()} | Stock: ${p.stock}</option>`;
            });

            select.disabled = false;

            if (productosGlobal.some(p => p.id == prevValue)) {
                select.value = prevValue;
            }
        });

        validarProductosRepetidos();
    }

    async function cargarBodegasDestino() {
        if (tipoMovimiento !== 'TRASLADO') return;
        if (!bodegaSelect.value) return;

        destinoSelect.innerHTML = `<option value="">Cargando...</option>`;

        try {
            const res = await fetch(`/solicitud/bodegas-destino/${bodegaSelect.value}`);
            const bodegasDest = await res.json();

            destinoSelect.innerHTML = `<option value="">Seleccione bodega destino</option>`;
            bodegasDest.forEach(b => {
                destinoSelect.innerHTML += `<option value="${b.id}">${b.nombre.toUpperCase()}</option>`;
            });

        } catch {
            destinoSelect.innerHTML = `<option value="">Seleccione bodega destino</option>`;
            Swal.fire("Error", "No se pudieron cargar las bodegas destino", "error");
        }
    }

    bodegaSelect.addEventListener('change', async () => {
        productosGlobal = [];
        limpiarSelectsProductos();

        if (!bodegaSelect.value) return;

        await cargarBodegasDestino();

        try {
            const res = await fetch(`/solicitud/bodega/${bodegaSelect.value}/departamento/${departamentoId}/productos`);
            productosGlobal = await res.json();
            actualizarSelectsProductos();
        } catch {
            Swal.fire("Error", "No se pudieron cargar los productos", "error");
        }
    });

    btnAdd.addEventListener('click', () => {
        const base = container.firstElementChild;
        const clone = base.cloneNode(true);

        clone.style.opacity = "0";
        clone.style.transform = "translateY(-10px)";

        clone.querySelectorAll("input, select").forEach(el => {
            let name = el.getAttribute("name");
            name = name.replace(/\[\d+\]/, `[${index}]`);
            el.setAttribute("name", name);

            if (el.tagName === "INPUT") el.value = "";
            if (el.classList.contains('producto')) {
                el.innerHTML = `<option value="">Seleccionar producto</option>`;
            }
        });

        container.appendChild(clone);
        index++;

        actualizarSelectsProductos();

        setTimeout(() => {
            clone.style.transition = "0.3s";
            clone.style.opacity = "1";
            clone.style.transform = "translateY(0)";
        }, 10);
    });

    container.addEventListener("click", e => {
        if (e.target.closest(".btn-remove") && container.children.length > 1) {
            const fila = e.target.closest(".producto-item");

            fila.style.transition = "0.3s";
            fila.style.opacity = "0";
            fila.style.transform = "translateX(-50px)";

            setTimeout(() => fila.remove(), 300);
        }

        validarProductosRepetidos();
    });

    container.addEventListener("change", e => {
        if (e.target.classList.contains("producto")) {
            validarProductosRepetidos();
        }

        if (e.target.name.includes("[cantidad]")) {
            const fila = e.target.closest(".producto-item");
            validarStock(fila);
        }
    });

    function validarStock(fila) {
        const select = fila.querySelector('.producto');
        const cantidadInput = fila.querySelector('input[name*="[cantidad]"]');
        const producto = productosGlobal.find(p => p.id == select.value);

        if (!producto) return;

        const cantidad = parseInt(cantidadInput.value ?? 0);

        if (cantidad > producto.stock) {
            cantidadInput.classList.add('is-invalid');

            Swal.fire({
                icon: 'warning',
                title: 'Stock insuficiente',
                text: `Solo hay ${producto.stock} unidades disponibles.`,
                confirmButtonColor: '#dc3545'
            });

        } else {
            cantidadInput.classList.remove('is-invalid');
        }
    }

    function validarProductosRepetidos() {
        const selects = [...document.querySelectorAll('.producto')];
        const values = selects.map(s => s.value).filter(v => v !== "");
        const repetidos = values.filter((v, i, arr) => arr.indexOf(v) !== i);

        selects.forEach(select => {
            if (repetidos.includes(select.value) && select.value !== "") {
                select.classList.add('is-invalid');

                Swal.fire({
                    icon: 'error',
                    title: 'Producto repetido',
                    text: 'Este producto ya fue seleccionado.',
                    confirmButtonColor: '#dc3545'
                });

            } else {
                select.classList.remove('is-invalid');
            }
        });
    }

    btnPreview.addEventListener("click", () => {
        let errores = [];

        const nombre    = document.querySelector("input[name='solicitante']").value.trim();
        const telefono  = telefonoInput.value.trim();
        const correo    = document.querySelector("input[name='correo']").value.trim();

        const fechaReq  = fechaRequeridaInput ? fechaRequeridaInput.value.trim() : '';
        const bodegaVal = bodegaSelect.value;

        if (nombre === "") errores.push("El nombre del solicitante es obligatorio.");

        if (telefono === "") {
            errores.push("El teléfono es obligatorio.");
        } else if (!/^\d{4}-\d{4}$/.test(telefono)) {
            errores.push("Formato de teléfono inválido (use 0000-0000).");
        }

        if (correo === "") errores.push("El correo es obligatorio.");

        // fecha requerida para SALIDA/TRASLADO
        if (tipoMovimiento !== 'PRESTAMO') {
            if (fechaReq === "") {
                errores.push("La fecha requerida es obligatoria.");
            } else {
                const hoy = new Date(); hoy.setHours(0,0,0,0);
                const fechaUsuario = new Date(fechaReq); fechaUsuario.setHours(0,0,0,0);
                if (fechaUsuario < hoy) errores.push("La fecha requerida no puede ser anterior a hoy.");
            }
        }

        // PRESTAMO: fecha_prestamo (UI) y fecha_devolucion
        if (tipoMovimiento === 'PRESTAMO') {
            const fPrestamo = fechaPrestamoInput?.value || '';
            const fDevol = fechaDevolucionInput?.value || '';

            if (!fPrestamo) errores.push("Debe seleccionar la fecha de préstamo (salida).");
            if (!fDevol) errores.push("Debe seleccionar la fecha de devolución.");

            if (fPrestamo) {
                // backend = fecha_requerida
                fechaRequeridaInput.value = fPrestamo;
            }

            if (fPrestamo && fDevol) {
                const a = new Date(fPrestamo); a.setHours(0,0,0,0);
                const b = new Date(fDevol); b.setHours(0,0,0,0);
                if (b < a) errores.push("La fecha de devolución no puede ser anterior a la fecha de préstamo.");
            }
        }

        if (!bodegaVal) errores.push("Debe seleccionar una bodega.");

        // TRASLADO
        if (tipoMovimiento === 'TRASLADO') {
            const destinoVal = destinoSelect.value;
            if (!destinoVal) errores.push("Debe seleccionar la bodega destino.");
            if (destinoVal && destinoVal === bodegaVal) errores.push("La bodega destino debe ser diferente a la bodega origen.");
        }

        const prodSelects = [...document.querySelectorAll(".producto")];
        if (!prodSelects.some(s => s.value !== "")) errores.push("Debe seleccionar al menos un producto.");

        if (document.querySelector(".producto.is-invalid")) errores.push("Revise productos repetidos.");

        if (document.querySelector("input[name*='[cantidad]'].is-invalid"))
            errores.push("Hay cantidades que exceden el stock disponible.");

        document.querySelectorAll("input[name*='[cantidad]']").forEach(cant => {
            if (cant.value.trim() === "" || parseInt(cant.value) <= 0) {
                errores.push("Las cantidades deben ser mayores a 0.");
                cant.classList.add("is-invalid");
            } else {
                cant.classList.remove("is-invalid");
            }
        });

        if (errores.length > 0) {
            Swal.fire({
                icon: "error",
                title: "Corrija los siguientes errores",
                html: errores.join("<br>"),
                confirmButtonColor: "#dc3545"
            });
            return;
        }

        mostrarVistaPrevia();
    });

    function mostrarVistaPrevia() {
        const solicitante = document.querySelector("input[name='solicitante']").value;
        const telefono    = telefonoInput.value;
        const correo      = document.querySelector("input[name='correo']").value;

        const bodega       = bodegaSelect.options[bodegaSelect.selectedIndex]?.text ?? '';
        const departamento = @json($sol->departamento->nombre ?? '');

        const bodegaDestino = (tipoMovimiento === 'TRASLADO')
            ? (destinoSelect.options[destinoSelect.selectedIndex]?.text ?? '')
            : '';

        const fPrestamo = fechaPrestamoInput?.value || '';
        const fDevol = fechaDevolucionInput?.value || '';

        let productosHTML = "";

        document.querySelectorAll(".producto-item").forEach(fila => {
            const select = fila.querySelector(".producto");
            const txt = select.options[select.selectedIndex]?.text ?? "";

            const cantidad = fila.querySelector("input[name*='[cantidad]']").value;
            const obs      = fila.querySelector("input[name*='[observacion]']").value;

            productosHTML += `
                <tr>
                    <td>${txt}</td>
                    <td class="text-center"><strong>${cantidad}</strong></td>
                    <td>${obs}</td>
                </tr>
            `;
        });

        document.getElementById("preview-content").innerHTML = `
            <div style="max-width:900px; margin:auto;">

                <div class="text-center mt-3">
                    <img src="/img/LAPAZESTE.png" width="80"
                        style="filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.2));">
                </div>

                <h2 class="text-center mt-3 mb-1 fw-bold text-uppercase fs-4" style="letter-spacing:1px;">
                    ${tipoMovimiento === 'SALIDA' ? 'SALIDA DE PRODUCTOS'
                        : (tipoMovimiento === 'PRESTAMO' ? 'PRÉSTAMO DE PRODUCTOS'
                        : (tipoMovimiento === 'TRASLADO' ? 'TRASLADO DE PRODUCTOS' : 'SOLICITUD DE PRODUCTOS'))}
                </h2>

                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-4" style="font-size:15px;">

                        <div class="px-3 py-2 mb-3 text-white fw-bold"
                            style="background:#004d66; border-radius:5px;">
                            Datos del solicitante:
                        </div>

                        <div class="ms-2">
                            <p><strong>Nombre:</strong> ${solicitante}</p>
                            <p><strong>Teléfono:</strong> ${telefono}</p>
                            <p><strong>Correo:</strong> ${correo}</p>
                        </div>

                        <div class="px-3 py-2 mt-4 mb-3 text-white fw-bold"
                            style="background:#004d66; border-radius:5px;">
                            Información de la solicitud:
                        </div>

                        <div class="ms-2">
                            <p><strong>Tipo:</strong> ${tipoMovimiento}</p>
                            <p><strong>Bodega origen:</strong> ${bodega}</p>
                            ${tipoMovimiento === 'TRASLADO' ? `<p><strong>Bodega destino:</strong> ${bodegaDestino}</p>` : ``}
                            ${tipoMovimiento === 'PRESTAMO' ? `
                                <p><strong>Fecha préstamo:</strong> ${fPrestamo}</p>
                                <p><strong>Fecha devolución:</strong> ${fDevol}</p>
                            ` : ``}
                            <p><strong>Departamento:</strong> ${departamento}</p>
                        </div>

                        <div class="px-3 py-2 mt-4 mb-2 text-white fw-bold"
                            style="background:#004d66; border-radius:5px;">
                            Productos solicitados:
                        </div>

                        <table class="table table-bordered" style="font-size:14px;">
                            <thead>
                                <tr style="background:#004d66; color:white;">
                                    <th>PRODUCTO</th>
                                    <th style="width:120px;">CANTIDAD</th>
                                    <th>OBSERVACIÓN</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${productosHTML}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;

        new bootstrap.Modal(document.getElementById('previewModal')).show();
    }

    btnEnviar.addEventListener("click", async () => {
        if (btnEnviar.disabled) return;

        // PRESTAMO: asegurar sincronización antes de enviar
        if (tipoMovimiento === 'PRESTAMO') {
            const fPrestamo = fechaPrestamoInput?.value || '';
            const fDevol = fechaDevolucionInput?.value || '';

            if (!fPrestamo || !fDevol) {
                Swal.fire("Falta información", "Debe completar las fechas de préstamo y devolución.", "warning");
                return;
            }

            fechaRequeridaInput.value = fPrestamo;

            const a = new Date(fPrestamo); a.setHours(0,0,0,0);
            const b = new Date(fDevol); b.setHours(0,0,0,0);
            if (b < a) {
                Swal.fire("Error", "La fecha de devolución no puede ser anterior a la fecha de préstamo.", "error");
                return;
            }
        }

        // TRASLADO: validar destino
        if (tipoMovimiento === 'TRASLADO') {
            const origen = bodegaSelect.value;
            const destino = destinoSelect.value;

            if (!destino) {
                Swal.fire("Falta información", "Debe seleccionar la bodega destino.", "warning");
                return;
            }

            if (destino === origen) {
                Swal.fire("Error", "La bodega destino debe ser diferente a la bodega origen.", "error");
                return;
            }
        }

        const form = document.getElementById("form-solicitud");
        const formData = new FormData(form);

        btnEnviar.disabled = true;
        btnEnviar.innerHTML = "<span class='spinner-border spinner-border-sm'></span> Procesando...";
        const pdfWindow = window.open('about:blank', '_blank');

        try {
            const res = await fetch(form.action, {
                method: "POST",
                body: formData,
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });

            const data = await res.json();

            if (!res.ok || !data.ok) throw new Error(data.error || data.message || 'No se pudo enviar la solicitud.');

            const modal = bootstrap.Modal.getInstance(document.getElementById("previewModal"));
            modal.hide();

            Swal.fire({
                title: "Solicitud enviada correctamente",
                html: `
                    Su solicitud ha sido registrada y procesada exitosamente.<br>
                    El documento PDF se ha generado y está disponible para su descarga.<br><br>
                    ¿Desea realizar una nueva solicitud o salir del sistema?
                `,
                icon: "success",
                showCancelButton: true,
                confirmButtonColor: "#0d6efd",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Nueva solicitud",
                cancelButtonText: "Salir"
            }).then((result) => {
                if (result.isConfirmed) {
                    form.reset();
                    container.innerHTML = "";
                    agregarFilaInicial();
                    limpiarSelectsProductos();

                    if (tipoMovimiento === 'TRASLADO') {
                        destinoSelect.innerHTML = `<option value="">Seleccione bodega destino</option>`;
                        wrapDestino.style.display = 'block';
                    }

                    if (tipoMovimiento === 'PRESTAMO') {
                        if (fechaPrestamoInput) fechaPrestamoInput.value = '';
                        if (fechaDevolucionInput) fechaDevolucionInput.value = '';
                        if (fechaRequeridaInput) fechaRequeridaInput.value = '';
                    }

                } else {
                    window.location.href = "{{ route('solicitud.logout') }}";
                }
            });

            if (pdfWindow) {
                pdfWindow.location.replace(data.pdf_url);
            } else {
                const link = document.createElement('a');
                link.href = data.pdf_url;
                link.target = '_blank';
                link.rel = 'noopener';
                link.textContent = 'Abrir PDF de la solicitud';
                document.getElementById('form-solicitud').prepend(link);
            }

        } catch (err) {
            if (pdfWindow) pdfWindow.close();
            Swal.fire({
                icon: "error",
                title: "Error",
                text: err.message,
            });
        }

        btnEnviar.disabled = false;
        btnEnviar.innerHTML = `<i class="bi bi-printer"></i> Generar PDF y Enviar`;
    });

    function agregarFilaInicial() {
        const fila = document.createElement("div");
        fila.className = "producto-item row g-3 align-items-end";

        fila.innerHTML = `
            <div class="col-md-6">
                <label class="form-label fw-semibold">Producto</label>
                <select name="productos[0][producto_id]" class="form-select producto" disabled>
                    <option value="">Seleccione una bodega primero</option>
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
                <button type="button" class="btn btn-danger btn-remove"><i class="bi bi-x-lg"></i></button>
            </div>
        `;

        container.appendChild(fila);
        index = 1;
    }

});
</script>
@endpush
