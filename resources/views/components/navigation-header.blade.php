<!-- Topbar -->
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow-sm">

    <!-- Sidebar Toggle (Mobile) -->
    <button id="sidebarToggleTop" type="button" class="btn btn-link d-md-none rounded-circle me-3" aria-label="Abrir o cerrar menú lateral" aria-controls="accordionSidebar">
        <i class="fa fa-bars fa-lg"></i>
    </button>

    <div class="d-flex align-items-center ms-auto">
        <!-- Topbar Navbar -->
        <ul class="navbar-nav">

            <!-- User Info Dropdown -->
            <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="me-2 d-none d-lg-inline text-gray-600 small">{{ Auth::user()->nombre }}</span>
                    <img class="img-profile rounded-circle" src="{{ asset('img/logo.png') }}" style="width: 40px; height: 40px; object-fit: cover;">
                </a>

                <!-- Dropdown - User -->
                <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in"
                    aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">
                        <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                        Perfil
                    </a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Cerrar sesión
                        </button>
                    </form>
                </div>

            </li>
        </ul>
    </div>

    <!-- Modal para mostrar la información del perfil -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="profileModalLabel">
                        <i class="fas fa-user-circle me-2"></i>Perfil de Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" value="{{ Auth::user()->nombre }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="usuario" class="form-label">Usuario</label>
                            <input type="text" class="form-control" id="usuario" value="{{ Auth::user()->usuario }}" disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <input type="text" class="form-control" id="estado" value="{{ Auth::user()->estado ? 'Activo' : 'Inactivo' }}" disabled>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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



