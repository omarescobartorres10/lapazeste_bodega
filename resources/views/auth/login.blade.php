<!doctype html>
<html lang="{{ str_replace('_', '_', app()->getLocale()) }}">

<head>
    <title>SISTEMA BODEGA - LOGIN</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
    <link rel="stylesheet" href="{{ asset('assets/styleLogin.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="shortcut icon" href="{{ asset('img/LAPAZESTE.png') }}" type="image/x-icon">

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <section class="vh-100" style="background-color: #0b3f83;">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col col-xl-10">
                    <div class="card" style="border-radius: 1rem;">
                        <div class="row g-0">
                            <div class="col-md-6 col-lg-5 d-none d-md-block" style="display: flex; justify-content: center; align-items: center; height: 100%;">
                                <img src="{{ asset('img/logo.png') }}" alt="login form" class="img-fluid" style="max-width: 80%; height: auto;" />
                            </div>
                            <div class="col-md-6 col-lg-7 d-flex align-items-center">
                                <div class="card-body p-4 p-lg-5 text-black">
                                    
                                    <!-- Toggle between Login and Register Form -->
                                    <div id="login-form">
                                        <form method="POST" action="{{ url('/login') }}">
                                            @csrf
                                            <div class="text-center mb-4">
                                                <h1 class="fw-bold mb-1">INICIAR SESIÓN</h1>
                                                <hr class="my-2" style="border-top: 3px solid #838282; width: 85%; margin: 0 auto;">
                                                <p class="text-muted custom-text">Sistema de Gestion de Almacen (SGA)</p>
                                                <p class="text-muted custom-text">ALCALDIA MUNICIPAL - LA PAZ ESTE</p>
                                            </div>

                                            <div class="form-outline mb-4">
                                                <input type="text" id="usuario" name="usuario" class="form-control form-control-lg" required placeholder="USUARIO" />
                                            </div>
                                            
                                            <div class="form-outline mb-4">
                                                <input type="password" id="password" name="password" class="form-control form-control-lg" required placeholder="CONTRASEÑA"/>
                                            </div>

                                            <div class="text-center mt-4 pt-2">
                                                <button type="submit" class="btn btn-lg px-5" style="background-color: #0b3f83; color: #fff; border-radius: 50px;">
                                                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar sesión
                                                </button>
                                            </div>

                                            <!-- Ícono para crear usuario (oculto a simple vista) -->                                            
                                            <!-- <div style="position: fixed; bottom: 15px; right: 20px;">
                                                <button type="button" onclick="validarCodigoSeguridad()" class="btn btn-sm btn-light rounded-circle shadow" title="Crear usuario">
                                                    <i class="bi bi-person-plus"></i>
                                                </button>
                                            </div> -->
                                        </form>

                                        <!-- Modal de crear usuario -->
                                        <div class="modal fade" id="crearUsuarioModal" tabindex="-1" aria-labelledby="crearUsuarioModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                            <form method="POST" action="{{ route('usuarios.store') }}">
                                                @csrf
                                                <div class="modal-content rounded-4 shadow-lg">
                                                <div class="modal-header bg-primary text-white rounded-top-4">
                                                    <h5 class="modal-title fw-semibold" id="crearUsuarioModalLabel">
                                                    <i class="bi bi-person-plus me-2"></i>Crear nuevo usuario
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                </div>
                                        
                                                <div class="modal-body p-4">
                                                    <div class="mb-3">
                                                    <input type="text" class="form-control rounded-pill px-3" name="nombre" required placeholder="NOMBRE">
                                                    </div>
                                        
                                                    <div class="mb-3">
                                                    <input type="text" class="form-control rounded-pill px-3" name="usuario" required placeholder="USUARIO">
                                                    </div>
                                        
                                                    <div class="mb-3">
                                                    <input type="password" class="form-control rounded-pill px-3" name="password" required placeholder="CONTRASEÑA">
                                                    </div>
                                                </div>
                                        
                                                <div class="modal-footer bg-light border-top-0 rounded-bottom-4 px-4 py-3">
                                                    <button type="submit" class="btn btn-success rounded-pill px-4">
                                                    <i class="bi bi-check-circle me-1"></i> Guardar
                                                    </button>
                                                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                                                    <i class="bi bi-x-circle me-1"></i> Cancelar
                                                    </button>
                                                </div>
                                                </div>
                                            </form>
                                            </div>
                                        </div>
  

                                    </div>                                

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    {{-- CODIGO DE SEGURIDAD --}}
    {{-- CODIGO DE SEGURIDAD --}}
    {{-- CODIGO DE SEGURIDAD --}}
    {{-- CODIGO DE SEGURIDAD --}}
    {{-- CODIGO DE SEGURIDAD --}}
    <script>
        function validarCodigoSeguridad() {
            // Vaciar los campos del formulario de login
            document.getElementById('usuario').value = '';
            document.getElementById('password').value = '';
            Swal.fire({
                title: 'Código de seguridad',
                input: 'password',
                inputLabel: 'Ingrese el código',
                inputPlaceholder: 'Código de autorización...',
                showCancelButton: true,
                confirmButtonText: 'Verificar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("{{ route('codigo.validar') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ codigo: result.value })
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Vaciar los campos del formulario de login
                        document.getElementById('usuario').value = '';
                        document.getElementById('password').value = '';
                        if (data.valido) {
                            const modal = new bootstrap.Modal(document.getElementById('crearUsuarioModal'));
                            modal.show();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Código incorrecto',
                                text: 'El código ingresado no es válido.'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                }
            });
        }
    </script>
    
    
    

    <script>
        const crearUsuarioModal = document.getElementById('crearUsuarioModal');
        crearUsuarioModal.addEventListener('hidden.bs.modal', function () {
            crearUsuarioModal.querySelector('form').reset();
        });
    </script>
    


    <script>
        function toggleForm(form) {
            if(form === 'register') {
                document.getElementById('login-form').style.display = 'none';
                document.getElementById('register-form').style.display = 'block';
            } else {
                document.getElementById('login-form').style.display = 'block';
                document.getElementById('register-form').style.display = 'none';
            }
        }

        document.querySelector('form').addEventListener('submit', function(e) {
            let usuario = document.querySelector('#usuario').value;
            let contraseña = document.querySelector('#password').value;

            if (usuario === '' || contraseña === '') {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: '¡Advertencia!',
                    text: 'Por favor, complete todos los campos.',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    </script>

    @if (session('error'))
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de autenticación',
                    text: "{{ session('error') }}".replace(/&quot;/g, '"'),
                    confirmButtonText: 'Aceptar'
                });
            });
        </script>
    @endif

    @if (session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                Swal.fire({
                    icon: 'success',
                    title: '¡Accion Realizada!',
                    text: "{{ session('success') }}".replace(/&quot;/g, '"'),
                    confirmButtonText: 'Aceptar'
                });
            });
        </script>
    @endif

</body>

</html>
