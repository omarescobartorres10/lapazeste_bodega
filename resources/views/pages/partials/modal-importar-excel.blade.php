{{-- ================================================================
    MODAL: IMPORTAR ARTÍCULOS DESDE EXCEL
================================================================ --}}
<div class="modal fade" id="modalImportarExcel" tabindex="-1" aria-labelledby="modalImportarExcelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">

            {{-- Encabezado --}}
            <div class="modal-header text-white" style="background-color: #198754;">
                <h5 class="modal-title" id="modalImportarExcelLabel">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i> Importar Artículos desde Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            {{-- Cuerpo --}}
            <div class="modal-body">

                {{-- Instrucciones --}}
                <div class="alert alert-info rounded-3 mb-4">
                    <h6 class="alert-heading fw-bold mb-2">
                        <i class="bi bi-info-circle me-1"></i> Instrucciones
                    </h6>
                    <ol class="mb-2 ps-3" style="font-size: 0.9rem;">
                        <li>Descargue la <strong>plantilla de Excel</strong> con el botón de abajo.</li>
                        <li>Complete los datos de los artículos en la hoja <em>"Plantilla Artículos"</em>.</li>
                        <li>Los nombres de <strong>categoría, tipo de producto, unidad de medida, departamento y bodega</strong> deben coincidir exactamente con los registrados en el sistema.</li>
                        <li>Seleccione el archivo completado y haga clic en <strong>Importar</strong>.</li>
                    </ol>
                    <small class="text-muted">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Formatos aceptados: <strong>.xlsx, .xls</strong> — Tamaño máximo: <strong>5 MB</strong>
                    </small>
                </div>

                {{-- Botón descargar plantilla --}}
                <div class="d-flex justify-content-center mb-4">
                    <a href="{{ route('productos.plantilla') }}" class="btn btn-outline-success rounded-pill px-4 py-2">
                        <i class="bi bi-download me-2"></i> Descargar Plantilla Excel
                    </a>
                </div>

                <hr>

                {{-- Formulario de importación --}}
                <form id="formImportarExcel" action="{{ route('productos.importar') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="archivo_excel" class="form-label fw-semibold">
                            <i class="bi bi-file-earmark-arrow-up me-1"></i> Seleccionar archivo Excel
                        </label>
                        <input
                            type="file"
                            class="form-control"
                            id="archivo_excel"
                            name="archivo_excel"
                            accept=".xlsx,.xls"
                            required
                        >
                    </div>

                    {{-- Vista previa del nombre del archivo --}}
                    <div id="archivoSeleccionado" class="alert alert-light border rounded-3 d-none mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-excel text-success fs-4 me-2"></i>
                            <div>
                                <strong id="nombreArchivo"></strong><br>
                                <small id="tamanoArchivo" class="text-muted"></small>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            {{-- Pie --}}
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancelar
                </button>
                <button type="button" id="btnEjecutarImportacion" class="btn btn-success rounded-pill px-4" disabled>
                    <i class="bi bi-upload me-1"></i> Importar
                </button>
            </div>

        </div>
    </div>
</div>

{{-- Script del modal --}}
@push('js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const inputArchivo    = document.getElementById('archivo_excel');
    const btnImportar     = document.getElementById('btnEjecutarImportacion');
    const formImportar    = document.getElementById('formImportarExcel');
    const preview         = document.getElementById('archivoSeleccionado');
    const nombreArchivo   = document.getElementById('nombreArchivo');
    const tamanoArchivo   = document.getElementById('tamanoArchivo');

    if (!inputArchivo || !btnImportar || !formImportar) return;

    // Mostrar vista previa al seleccionar archivo
    inputArchivo.addEventListener('change', () => {
        const file = inputArchivo.files[0];

        if (file) {
            // Validar extensión
            const ext = file.name.split('.').pop().toLowerCase();
            if (!['xlsx', 'xls'].includes(ext)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Formato no válido',
                    text: 'Solo se permiten archivos .xlsx o .xls'
                });
                inputArchivo.value = '';
                btnImportar.disabled = true;
                preview.classList.add('d-none');
                return;
            }

            // Validar tamaño (5 MB)
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'Archivo muy grande',
                    text: 'El archivo no puede pesar más de 5 MB.'
                });
                inputArchivo.value = '';
                btnImportar.disabled = true;
                preview.classList.add('d-none');
                return;
            }

            nombreArchivo.textContent = file.name;
            const sizeMB = (file.size / 1024 / 1024).toFixed(2);
            tamanoArchivo.textContent = `${sizeMB} MB`;
            preview.classList.remove('d-none');
            btnImportar.disabled = false;
        } else {
            preview.classList.add('d-none');
            btnImportar.disabled = true;
        }
    });

    // Ejecutar importación con confirmación
    btnImportar.addEventListener('click', () => {
        Swal.fire({
            title: '¿Importar artículos?',
            text: 'Se registrarán los artículos contenidos en el archivo Excel.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, importar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loader
                btnImportar.disabled = true;
                btnImportar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Importando...';

                formImportar.submit();
            }
        });
    });

    // Resetear modal al cerrarse
    const modal = document.getElementById('modalImportarExcel');
    modal.addEventListener('hidden.bs.modal', () => {
        inputArchivo.value = '';
        preview.classList.add('d-none');
        btnImportar.disabled = true;
        btnImportar.innerHTML = '<i class="bi bi-upload me-1"></i> Importar';
    });
});
</script>
@endpush

