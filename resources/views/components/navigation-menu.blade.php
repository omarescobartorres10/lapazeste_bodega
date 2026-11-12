

<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('panel') }}">
        <div class="sidebar-brand-icon">
            <img class="img-profile rounded-circle" src="{{ asset('logo-blanco.png') }}" style="width: 45px; height: 45px; object-fit: contain;">
        </div>
        <div class="sidebar-brand-text mx-3 d-none d-md-inline">LA PAZ ESTE</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Panel Heading -->
    <div class="sidebar-heading text-center">
        PANEL
    </div>

    <!-- Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="{{ route('panel') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading1 text-center fw-bold">
        MANTENIMIENTO
    </div>

    <!-- INVENTARIO -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseOne">
            <i class="fas fa-fw fa-cube"></i>
            <span>INVENTARIO</span>
        </a>
        <div id="collapseOne" class="collapse" data-bs-parent="#accordionSidebar">
            <div class="bg-light py-2 collapse-inner rounded shadow-sm">
                <h6 class="collapse-header text-primary fw-bold">Gestión de Articulos</h6>
                <a class="collapse-item" href="{{ route('productos') }}"><i class="fas fa-box-open"></i> Artículos</a>
                <a class="collapse-item" href="{{ route('categorias') }}"><i class="fas fa-tags"></i> Categorías</a>

                @auth
                    @if(!Auth::user()->bodega)
                        <a class="collapse-item" href="{{ route('usuarios') }}">
                            <i class="fas fa-users-cog"></i> Usuarios
                        </a>

                        <a class="collapse-item" href="{{ route('solicitantes.index') }}">
                            <i class="fas fa-users-cog"></i> Solicitantes
                        </a>

                        <a class="collapse-item" href="{{ route('bodegas.index') }}">
                            <i class="fas fa-warehouse"></i> Bodegas
                        </a>
                    @endif
                @endauth

                <a class="collapse-item" href="{{ route('departamentos') }}"><i class="fas fa-building"></i> Departamentos</a>
                <a class="collapse-item" href="{{ route('tipoProducto') }}"><i class="fas fa-th-list"></i> Tipo de Artículo</a>
                <a class="collapse-item" href="{{ route('unidades') }}"><i class="fas fa-balance-scale"></i> Unidad de Medida</a>
            </div>
        </div>
    </li>

    <!-- ENTRADAS -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
            <i class="fas fa-fw fa-truck"></i>
            <span>ENTRADAS</span>
        </a>
        <div id="collapseTwo" class="collapse" data-bs-parent="#accordionSidebar">
            <div class="bg-light py-2 collapse-inner rounded shadow-sm">
                <h6 class="collapse-header text-success fw-bold">Gestión de Entradas</h6>
                <a class="collapse-item" href="{{ route('entradas') }}"><i class="fas fa-plus-circle"></i> Agregar Entradas</a>
                <a class="collapse-item" href="{{ route('entradas.historial') }}"><i class="bi bi-journal-text me-2"></i> Historial de Entradas</a>
            </div>
        </div>
    </li>

    <!-- SALIDAS -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseUtilities">
            <i class="fas fa-fw fa-arrow-alt-circle-up"></i>
            <span>SALIDAS</span>
        </a>
        <div id="collapseUtilities" class="collapse" data-bs-parent="#accordionSidebar">
            <div class="bg-light py-2 collapse-inner rounded shadow-sm">
                <h6 class="collapse-header text-danger fw-bold">Gestión de Salidas</h6>
                <a class="collapse-item" href="{{ route('salidas') }}"><i class="fas fa-minus-circle"></i> Agregar Salida</a>
                <a class="collapse-item" href="{{ route('salidas.historial') }}"><i class="bi bi-journal-text me-2"></i> Historial de Salidas</a>
            </div>
        </div>
    </li>

    <!-- PRESTAMOS -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseFour">
            <i class="fas fa-exchange-alt"></i>
            <span>PRESTAMOS</span>
        </a>
        <div id="collapseFour" class="collapse" data-bs-parent="#accordionSidebar">
            <div class="bg-light py-2 collapse-inner rounded shadow-sm">
                <h6 class="collapse-header text-info fw-bold">Gestión de Préstamos</h6>
                <a class="collapse-item" href="{{ route('prestamos') }}"><i class="fas fa-handshake"></i> Agregar Préstamo</a>
                <a class="collapse-item" href="{{ route('prestamos.historial') }}"><i class="bi bi-journal-text me-2"></i> Historial de Prestamos</a>
            </div>
        </div>
    </li>

    <!-- ✅ TRASLADOS (NUEVO) -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseTraslados" aria-expanded="false" aria-controls="collapseTraslados">
            <i class="fas fa-truck-moving"></i>
            <span>TRASLADOS</span>
        </a>
        <div id="collapseTraslados" class="collapse" data-bs-parent="#accordionSidebar">
            <div class="bg-light py-2 collapse-inner rounded shadow-sm">
                <h6 class="collapse-header text-primary fw-bold">Gestión de Traslados</h6>

                {{-- Por ahora solo historial (porque el traslado lo haces desde SOLICITUDES -> ENTREGAR) --}}
                <a class="collapse-item" href="{{ route('traslados.historial') }}">
                    <i class="bi bi-journal-text me-2"></i> Historial de Traslados
                </a>

                {{-- Si después haces un formulario manual para traslados, descomenta esto:
                <a class="collapse-item" href="{{ route('traslados') }}">
                    <i class="fas fa-plus-circle"></i> Nuevo Traslado
                </a>
                --}}
            </div>
        </div>
    </li>

    <!-- BAJAS -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseFive">
            <i class="fas fa-trash-alt"></i>
            <span>BAJAS</span>
        </a>
        <div id="collapseFive" class="collapse" data-bs-parent="#accordionSidebar">
            <div class="bg-light py-2 collapse-inner rounded shadow-sm">
                <h6 class="collapse-header text-danger fw-bold">Gestión de Bajas</h6>
                <a class="collapse-item" href="{{ route('bajas') }}"><i class="fas fa-trash"></i> Agregar Baja</a>
                <a class="collapse-item" href="{{ route('bajas.historial') }}"><i class="bi bi-journal-text me-2"></i> Historial de Bajas</a>
            </div>
        </div>
    </li>

    <!-- SOLICITUDES -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSolicitudes">
            <i class="fas fa-envelope-open-text"></i>

            <span>
                SOLICITUDES
                @if(isset($solicitudesPendientes) && $solicitudesPendientes > 0)
                    <span class="badge badge-warning ml-2">
                        {{ $solicitudesPendientes }}
                    </span>
                @endif
            </span>
        </a>

        <div id="collapseSolicitudes" class="collapse" data-bs-parent="#accordionSidebar">
            <div class="bg-light py-2 collapse-inner rounded shadow-sm">
                <h6 class="collapse-header text-primary fw-bold">Gestión de Solicitudes</h6>

                <a class="collapse-item" href="{{ route('solicitud.index') }}">
                    <i class="fas fa-list"></i> Listado de Solicitudes
                </a>
            </div>
        </div>
    </li>

    <hr class="sidebar-divider">

</ul>