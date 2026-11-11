<!-- Topbar -->
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 shadow-sm justify-content-end">

    <!-- Sidebar Toggle (Mobile) -->
    <button id="sidebarToggleTop" type="button" class="btn btn-link d-md-none rounded-circle me-3" aria-label="Abrir o cerrar menú lateral" aria-controls="accordionSidebar">
        <i class="fa fa-bars"></i>
    </button>

    <div class="d-flex align-items-center ms-auto">
        <!-- Topbar Navbar -->
        <ul class="navbar-nav">

            <!-- User Info Dropdown -->
            <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="me-2 d-none d-lg-inline text-gray-700 small fw-bold">
                        {{ Auth::user()->nombre }}
                    </span>                    
                    <img class="img-profile rounded-circle" src="{{ asset('img/logo.png') }}" style="width: 40px; height: 40px;">
                </a>

                <!-- Dropdown Menu -->
                <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 animated--fade-in"
                    aria-labelledby="userDropdown"
                    style="min-width: 200px; background: #ffffffcc; backdrop-filter: blur(6px);">
                    
                    <!-- Perfil -->
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2 rounded-3 hover-bg" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">
                        <i class="fas fa-user fa-sm fa-fw text-primary"></i>
                        <span class="fw-semibold">Perfil</span>
                    </a>

                    <div class="dropdown-divider my-1"></div>

                    <!-- Logout -->
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 py-2 rounded-3 hover-bg">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw text-danger"></i>
                            <span class="fw-semibold text-danger">Cerrar sesión</span>
                        </button>
                    </form>
                </div>

            </li>
        </ul>
    </div>

    <!-- Modal para mostrar la información del perfil -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-5 shadow-lg" style="background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px);">
                <div class="modal-header bg-gradient-primary text-white rounded-top-5" style="background: linear-gradient(135deg, #3b82f6, #06b6d4);">
                    <h5 class="modal-title" id="profileModalLabel">
                        <i class="fas fa-user-circle me-2"></i>Perfil de Usuario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <form>
                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-bold">Nombre</label>
                            <input type="text" class="form-control border-0 shadow-sm rounded-3" id="nombre" value="{{ Auth::user()->nombre }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="usuario" class="form-label fw-bold">Usuario</label>
                            <input type="text" class="form-control border-0 shadow-sm rounded-3" id="usuario" value="{{ Auth::user()->usuario }}" disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label for="estado" class="form-label fw-bold">Estado</label>
                            <input type="text" class="form-control border-0 shadow-sm rounded-3" id="estado" value="{{ Auth::user()->estado ? 'Activo' : 'Inactivo' }}" disabled>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-transparent border-top-0">
                    <button type="button" class="btn btn-outline-primary rounded-pill px-4 shadow-sm" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>


</nav>



<script>
    document.getElementById("sidebarToggleTop").addEventListener("click", function () {
        const sidebar = document.getElementById("accordionSidebar");
        sidebar.classList.toggle("toggled");

        // Si deseas mover el contenido, puedes alternar una clase en el body
        document.body.classList.toggle("toggled");
    });
</script>



