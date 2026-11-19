@extends('layouts.public')

@section('title', 'Seleccione el tipo de movimiento')

@section('content')
<style>
.tipo-card {
    position: relative;
    border: 2px solid transparent;
    border-radius: 1rem;
    padding: 2rem 1.5rem;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    overflow: hidden;
    background: #fff;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}
.tipo-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    transition: height 0.3s ease;
}
.tipo-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.15);
}
.tipo-card:active {
    transform: translateY(-2px);
}
.tipo-card.prestamo::before { background: linear-gradient(90deg, #0d6efd, #6610f2); }
.tipo-card.salida::before { background: linear-gradient(90deg, #fd7e14, #dc3545); }
.tipo-card.traslado::before { background: linear-gradient(90deg, #198754, #20c997); }
.tipo-card:hover.prestamo { border-color: #0d6efd; }
.tipo-card:hover.salida { border-color: #fd7e14; }
.tipo-card:hover.traslado { border-color: #198754; }
.tipo-icon {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.8rem;
    color: #fff;
    transition: transform 0.3s ease;
}
.tipo-card:hover .tipo-icon {
    transform: scale(1.1) rotate(5deg);
}
.tipo-card.prestamo .tipo-icon { background: linear-gradient(135deg, #0d6efd, #6610f2); }
.tipo-card.salida .tipo-icon { background: linear-gradient(135deg, #fd7e14, #dc3545); }
.tipo-card.traslado .tipo-icon { background: linear-gradient(135deg, #198754, #20c997); }
.tipo-title {
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 0.3rem;
}
.tipo-card.prestamo .tipo-title { color: #0d6efd; }
.tipo-card.salida .tipo-title { color: #fd7e14; }
.tipo-card.traslado .tipo-title { color: #198754; }
.tipo-desc {
    font-size: 0.85rem;
    color: #6c757d;
    margin: 0;
}
.tipo-arrow {
    position: absolute;
    bottom: 1rem;
    right: 1rem;
    font-size: 1.2rem;
    opacity: 0;
    transform: translateX(-8px);
    transition: all 0.3s ease;
}
.tipo-card:hover .tipo-arrow {
    opacity: 1;
    transform: translateX(0);
}
.page-header-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(180deg, #4e73df 0%, #224abe 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
}
.page-header-icon img {
    width: 60px;
    height: 60px;
    object-fit: contain;
}
</style>

<div class="container py-5" style="max-width:900px;">
    <div class="text-center mb-4">
        <div class="page-header-icon">
            <img src="{{ asset('logo-blanco.png') }}" alt="La Paz Este">
        </div>
        <h3 class="fw-bold mb-1">¿Qué desea realizar?</h3>
        <p class="text-muted mb-0">Seleccione el tipo de movimiento para continuar</p>
    </div>

    <form id="formTipo" method="POST" action="{{ route('solicitud.tipo.guardar') }}">
        @csrf
        <input type="hidden" name="tipo_movimiento" id="tipo_movimiento">

        <div class="row g-4 justify-content-center">

            <!-- PRÉSTAMO -->
            <div class="col-md-4">
                <div class="tipo-card prestamo text-center" onclick="setTipo('PRESTAMO')">
                    <div class="tipo-icon">
                        <i class="bi bi-arrow-left-right"></i>
                    </div>
                    <div class="tipo-title">PRÉSTAMO</div>
                    <p class="tipo-desc">Solicitar productos en préstamo temporal</p>
                    <i class="bi bi-arrow-right tipo-arrow" style="color:#0d6efd;"></i>
                </div>
            </div>

            <!-- SALIDA -->
            <div class="col-md-4">
                <div class="tipo-card salida text-center" onclick="setTipo('SALIDA')">
                    <div class="tipo-icon">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </div>
                    <div class="tipo-title">SALIDA</div>
                    <p class="tipo-desc">Retirar productos de la bodega</p>
                    <i class="bi bi-arrow-right tipo-arrow" style="color:#fd7e14;"></i>
                </div>
            </div>

            <!-- TRASLADO -->
            <div class="col-md-4">
                <div class="tipo-card traslado text-center" onclick="setTipo('TRASLADO')">
                    <div class="tipo-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div class="tipo-title">TRASLADO</div>
                    <p class="tipo-desc">Mover productos a otra bodega</p>
                    <i class="bi bi-arrow-right tipo-arrow" style="color:#198754;"></i>
                </div>
            </div>

        </div>
    </form>

    <div class="text-center mt-4">
        <a href="{{ route('solicitud.logout') }}" class="btn btn-outline-danger btn-sm px-3">
            <i class="bi bi-box-arrow-right me-1"></i> Salir
        </a>
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