@extends('layouts.public')

@section('title', 'Seleccione el tipo de movimiento')

@section('content')
<div class="container py-5" style="max-width:800px;">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-4 text-center">
            <h3 class="fw-bold mb-2">¿Qué desea realizar?</h3>
            <p class="text-muted mb-4">Seleccione una opción para continuar</p>

            <form id="formTipo" method="POST" action="{{ route('solicitud.tipo.guardar') }}">
                @csrf
                <input type="hidden" name="tipo_movimiento" id="tipo_movimiento">

                <div class="d-grid gap-3">
                    <button type="button" class="btn btn-primary btn-lg" onclick="setTipo('PRESTAMO')">
                        <i class="bi bi-arrow-left-right me-2"></i> PRÉSTAMO DE PRODUCTOS
                    </button>

                    <button type="button" class="btn btn-warning btn-lg" onclick="setTipo('SALIDA')">
                        <i class="bi bi-box-arrow-up-right me-2"></i> SALIDA DE PRODUCTOS
                    </button>

                    <button type="button" class="btn btn-success btn-lg" onclick="setTipo('TRASLADO')">
                        <i class="bi bi-truck me-2"></i> TRASLADO A OTRA BODEGA
                    </button>
                </div>
            </form>

            <div class="mt-4">
                <a href="{{ route('solicitud.logout') }}" class="btn btn-outline-danger">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
function setTipo(tipo){
    document.getElementById('tipo_movimiento').value = tipo;
    document.getElementById('formTipo').submit();
}
</script>
@endpush