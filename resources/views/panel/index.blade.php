<head>
    <link rel="stylesheet" href="{{ asset('assets/tarjetasDashboard.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

@extends('welcome')

@section('title', 'Dashboard')

@section('content')

<div class="container-fluid py-4">

    <h2 class="mb-3 fw-bold text-center">📊 Panel de Control</h2>

    <!-- SELECT FILTRO BODEGA CENTRADO -->
    <div class="d-flex justify-content-center mb-4 dashboard-animate">
        <div class="text-center" style="width: 100%; max-width: 320px;">
            <label for="filtroBodega" 
                class="fw-semibold text-success mb-2 d-block" 
                style="font-size: .95rem;">
                Filtrar por bodega
            </label>

            <div class="input-group shadow-sm">
                <span class="input-group-text bg-success text-white">
                    <i class="bi bi-building"></i>
                </span>

                <select id="filtroBodega"
                        class="form-select border-success fw-semibold"
                        style="border-left: none;">
                    <option value="">Todas las bodegas</option>

                    @foreach ($bodegas as $bod)
                        <option value="{{ $bod->id }}"
                            {{ isset($bodegaSeleccionada) && $bodegaSeleccionada == $bod->id ? 'selected' : '' }}>
                            {{ strtoupper($bod->nombre) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>


    <!-- TARJETAS PRINCIPALES -->
    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-3 px-2 pb-2">

        <!-- Artículos -->
        <div class="col dashboard-animate">
            <div class="dashboard-card shadow-sm text-white"
                style="background: linear-gradient(145deg, #1e54d6, #3f73ff);">
                <i class="bi bi-box-seam dashboard-icon"></i>
                <div class="dashboard-title">ARTÍCULOS</div>
                <div class="dashboard-value">{{ $totalProductos }}</div>
            </div>
        </div>

        <!-- Entradas -->
        <div class="col dashboard-animate">
            <div class="dashboard-card shadow-sm text-white"
                style="background: linear-gradient(145deg,#17a35d,#42d67e);">
                <i class="bi bi-box-arrow-in-down dashboard-icon"></i>
                <div class="dashboard-title">ENTRADAS MES</div>
                <div class="dashboard-value">{{ $entradasMes }}</div>
            </div>
        </div>

        <!-- Salidas -->
        <div class="col dashboard-animate">
            <div class="dashboard-card shadow-sm text-white"
                style="background: linear-gradient(145deg,#c62828,#ff5252);">
                <i class="bi bi-box-arrow-up dashboard-icon"></i>
                <div class="dashboard-title">SALIDAS MES</div>
                <div class="dashboard-value">{{ $salidasMes }}</div>
            </div>
        </div>

        <!-- Préstamos activos -->
        <div class="col dashboard-animate">
            <div class="dashboard-card shadow-sm text-dark"
                style="background: linear-gradient(145deg,#f6e93c,#fff372);">
                <i class="bi bi-arrow-left-right dashboard-icon"></i>
                <div class="dashboard-title">PRÉSTAMOS ACTIVOS</div>
                <div class="dashboard-value">{{ $prestamosActivos }}</div>
            </div>
        </div>

        <!-- Bajas -->
        <div class="col dashboard-animate">
            <div class="dashboard-card shadow-sm text-white"
                style="background: linear-gradient(145deg,#d32f2f,#ff6f6f);">
                <i class="bi bi-exclamation-triangle-fill dashboard-icon"></i>
                <div class="dashboard-title">BAJAS MES</div>
                <div class="dashboard-value">{{ $bajasMes }}</div>
            </div>
        </div>

    </div>


    <!-- PRODUCTO MÁS SOLICITADO + PRODUCTO CON MÁS ENTRADAS -->
    <div class="row g-3 mt-3">

        <!-- Producto más solicitado -->
        <div class="col-md-6 dashboard-animate">
            <div class="card shadow-sm border-0 rounded-4 p-3 h-100">

                @if ($productoMasSalidas)
                    <div class="text-center">
                        <i class="bi bi-box-arrow-up text-primary" style="font-size: 2rem;"></i>
                        <h5 class="fw-bold mt-2 mb-1">Artículo más solicitado</h5>
                        <p class="fs-5 fw-semibold text-dark mb-0">{{ $productoMasSalidas->nombre }}</p>
                        <small class="text-secondary">
                            Total salidas: <strong>{{ $productoMasSalidas->total_salidas }}</strong>
                        </small>
                    </div>
                @else
                    <div class="alert alert-info text-center rounded-3 py-2 mb-0">
                        <i class="bi bi-info-circle me-2"></i> No hay salidas registradas aún.
                    </div>
                @endif

            </div>
        </div>

        <!-- Producto con más entradas -->
        <div class="col-md-6 dashboard-animate">
            <div class="card shadow-sm border-0 rounded-4 p-3 h-100">

                @if ($productoMasEntradas)
                    <div class="text-center">
                        <i class="bi bi-box-arrow-in-down text-success" style="font-size: 2rem;"></i>
                        <h5 class="fw-bold mt-2 mb-1">Artículo con más entradas</h5>
                        <p class="fs-5 fw-semibold text-dark mb-0">{{ $productoMasEntradas->nombre }}</p>
                        <small class="text-secondary">
                            Total entradas: <strong>{{ $productoMasEntradas->total_entradas }}</strong>
                        </small>
                    </div>
                @else
                    <div class="alert alert-info text-center rounded-3 py-2 mb-0">
                        <i class="bi bi-info-circle me-2"></i> No hay entradas registradas aún.
                    </div>
                @endif

            </div>
        </div>

    </div>


    <!-- BAJO STOCK + COMPARATIVA -->
    <div class="row mt-3">

        <!-- Artículos con bajo stock -->
        <div class="col-md-6 dashboard-animate">
            <div class="card shadow-sm border-0 rounded-4 h-100 d-flex flex-column">
                <div class="card-body">

                    <h5 class="fw-bold text-center mb-3">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                        Artículos con bajo stock
                    </h5>

                    @if (!$hayProductos)
                        <div class="alert alert-info text-center rounded-3 py-2">
                            <i class="bi bi-info-circle me-2"></i>Aún no hay productos registrados.
                        </div>

                    @elseif ($productosBajoStock->isEmpty())
                        <div class="alert alert-success text-center rounded-3 py-2">
                            <i class="bi bi-check-circle me-2"></i>Todos los productos tienen stock suficiente.
                        </div>

                    @else
                        <div style="min-height: 350px;">
                            <canvas id="bajoStockChart"></canvas>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        <!-- Comparativa mensual -->
        <div class="col-md-6 dashboard-animate">
            <div class="card shadow-sm border-0 rounded-4 h-100 d-flex flex-column">
                <div class="card-body">

                    <h5 class="fw-bold text-center mb-3">
                        <i class="bi bi-pie-chart-fill me-2 text-primary"></i>
                        Comparativa del mes
                    </h5>

                    @if ($entradasMes == 0 && $salidasMes == 0 && $prestamosMes == 0)
                        <div class="alert alert-info text-center rounded-3 py-2">
                            <i class="bi bi-info-circle me-2"></i>No hay datos para mostrar.
                        </div>
                    @else
                        <div style="width:100%; max-width:380px; margin: 0 auto;">
                            <canvas id="graficoComparativo"></canvas>
                        </div>
                    @endif

                </div>
            </div>
        </div>

    </div>


    <!-- TOP 5 -->
    <div class="row mt-3">

        <div class="col-md-6 dashboard-animate">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">

                    <h5 class="fw-bold mb-3 text-center">
                        <i class="bi bi-box-arrow-up text-danger me-2"></i>
                        Top 5 Artículos con más salidas
                    </h5>

                    @if ($topProductosSalidas->isEmpty())
                        <div class="alert alert-info text-center rounded-3 py-2">
                            <i class="bi bi-info-circle me-2"></i>No hay salidas registradas aún.
                        </div>

                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($topProductosSalidas as $producto)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold text-dark">{{ $producto->nombre }}</span>
                                    <span class="badge bg-danger-subtle text-danger-emphasis rounded-pill px-3 py-2 shadow-sm" style="font-size:.85rem;">
                                        {{ $producto->total }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                </div>
            </div>
        </div>


        <div class="col-md-6 dashboard-animate">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">

                    <h5 class="fw-bold mb-3 text-center">
                        <i class="bi bi-box-arrow-in-down text-success me-2"></i>
                        Top 5 Artículos con más entradas
                    </h5>

                    @if ($topProductosEntradas->isEmpty())
                        <div class="alert alert-info text-center rounded-3 py-2">
                            <i class="bi bi-info-circle me-2"></i>No hay entradas registradas aún.
                        </div>

                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($topProductosEntradas as $producto)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold text-dark">{{ $producto->nombre }}</span>
                                    <span class="badge bg-success-subtle text-success-emphasis rounded-pill px-3 py-2 shadow-sm" style="font-size:.85rem;">
                                        {{ $producto->total }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                </div>
            </div>
        </div>

    </div>


    <!-- ACTIVIDAD DIARIA -->
    <div class="row mt-4">
        <div class="col-12 dashboard-animate">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-primary text-white rounded-top-4">
                    <h5 class="mb-0 fw-bold">Actividad diaria — {{ $mesActual }}</h5>
                </div>

                <div class="card-body">
                    <div style="width: 100%; height: 300px;">
                        <canvas id="actividadDiariaChart"></canvas>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

@endsection



@push('js')

{{-- ===== FILTRO DE BODEGA ===== --}}
<script>
document.getElementById('filtroBodega').addEventListener('change', function () {
    const url = new URL(window.location.href);
    this.value ? url.searchParams.set('bodega_id', this.value)
               : url.searchParams.delete('bodega_id');
    window.location.href = url.toString();
});
</script>

<script>
document.getElementById('filtroBodega').addEventListener('change', () => {
    const el = document.getElementById('filtroBodega');
    el.classList.add('select-flash');
    setTimeout(() => el.classList.remove('select-flash'), 350);
});
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

{{-- ===================================== --}}
{{-- === 1) GRÁFICO DE BAJO STOCK ======== --}}
{{-- ===================================== --}}
<script>
@if($productosBajoStock->count() > 0)

const ctxStock = document.getElementById('bajoStockChart');

new Chart(ctxStock, {
    type: 'bar',
    data: {
        labels: @json($productosBajoStock->pluck('nombre')),
        datasets: [{
            label: 'Stock actual',
            data: @json($productosBajoStock->pluck('stock')),
            backgroundColor: 'rgba(46, 104, 255, 0.85)',
            borderRadius: 12,
            maxBarThickness: 45
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#111',
                titleColor: '#fff',
                bodyColor: '#fff',
                padding: 10
            }
        },
        scales: {
            x: {
                ticks: { font: { size: 11, weight: '600' } },
                grid: { display: false }
            },
            y: {
                beginAtZero: true,
                ticks: { precision: 0 }
            }
        }
    }
});
@endif
</script>

{{-- ===================================== --}}
{{-- === 2) GRÁFICO COMPARATIVO ========== --}}
{{-- ===================================== --}}
<script>
const entradas = {{ $entradasMes }};
const salidas  = {{ $salidasMes }};
const prestamos = {{ $prestamosMes }};

@if($entradasMes || $salidasMes || $prestamosMes)

new Chart(document.getElementById('graficoComparativo'), {
    type: 'doughnut',
    data: {
        labels: ['Entradas', 'Salidas', 'Préstamos'],
        datasets: [{
            data: [entradas, salidas, prestamos],
            backgroundColor: [
                'rgba(46, 104, 255, 0.85)', // azul
                'rgba(230, 57, 70, 0.85)',  // rojo
                'rgba(34, 197, 94, 0.85)'   // verde
            ],
            borderWidth: 0,
            hoverOffset: 14
        }]
    },
    options: {
        responsive: true,
        cutout: '62%',
        animation: { animateRotate: true, animateScale: true },
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    pointStyle: 'circle',
                    font: { size: 12, weight: '600' }
                }
            }
        }
    }
});
@endif
</script>

{{-- ===================================== --}}
{{-- === 3) ACTIVIDAD DIARIA ============ --}}
{{-- ===================================== --}}
<script>
const diasLabels = @json($diasDelMes);
const entradasDia = @json($valoresEntradas);
const salidasDia = @json($valoresSalidas);
const prestamosDia = @json($valoresPrestamos);

const c = document.getElementById('actividadDiariaChart');

new Chart(c, {
    type: 'line',
    data: {
        labels: diasLabels,
        datasets: [
            {
                label: 'Entradas',
                data: entradasDia,
                borderColor: 'rgba(46, 104, 255, 1)',
                backgroundColor: 'rgba(46, 104, 255, 0.25)',
                borderWidth: 2,
                fill: true,
                tension: 0.35,
                pointRadius: 3,
                pointBackgroundColor: 'rgba(46,104,255,1)'
            },
            {
                label: 'Salidas',
                data: salidasDia,
                borderColor: 'rgba(230,57,70,1)',
                backgroundColor: 'rgba(230,57,70,0.25)',
                borderWidth: 2,
                fill: true,
                tension: 0.35,
                pointRadius: 3,
                pointBackgroundColor: 'rgba(230,57,70,1)'
            },
            {
                label: 'Préstamos',
                data: prestamosDia,
                borderColor: 'rgba(34,197,94,1)',
                backgroundColor: 'rgba(34,197,94,0.25)',
                borderWidth: 2,
                fill: true,
                tension: 0.35,
                pointRadius: 3,
                pointBackgroundColor: 'rgba(34,197,94,1)'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { intersect: false, mode: 'index' },
        plugins: {
            legend: {
                position: 'bottom',
                labels: { usePointStyle: true, font: { size: 11 } }
            },
            tooltip: {
                backgroundColor: '#111',
                titleColor: '#fff',
                bodyColor: '#fff',
                padding: 10
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { precision: 0 }
            }
        }
    }
});
</script>

@endpush
