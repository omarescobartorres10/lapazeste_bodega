@extends('layouts.public')

@section('title', 'Validación de acceso')

@section('content')

<style>
    .login-card {
        width: 100%;
        max-width: 420px;
        border-radius: 20px;
        animation: fadeIn .3s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .login-container {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 40px 15px;
        min-height: 80vh;
    }

    .logo-header img {
        width: 80px;
        filter: drop-shadow(0px 3px 5px rgba(0,0,0,0.2));
    }
</style>

<div class="container login-container">

    <div class="card shadow-lg border-0 login-card p-4">

        <div class="text-center mb-3 logo-header">
            <img src="/img/LAPAZESTE.png" alt="Logo">
        </div>

        <h4 class="text-center fw-bold mb-2">Validación de acceso</h4>
        <p class="text-center text-muted mb-4">
            Ingrese su DUI y código de marcador para continuar
        </p>

        {{-- MENSAJE DE ERROR DEL BACKEND --}}
        @if(session('error'))
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error de acceso',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#d33'
                });
            </script>
        @endif

        <form id="loginForm" method="POST" action="{{ route('solicitud.autenticar') }}">
            @csrf

            {{-- DUI --}}
            <label class="form-label fw-semibold">DUI</label>
            <input type="text" class="form-control mb-3" id="dui"
                   name="dui" placeholder="00000000-0" maxlength="10">

            {{-- CÓDIGO --}}
            <label class="form-label fw-semibold">Código de Marcador</label>
            <input type="password" class="form-control mb-4" id="codigo"
                   name="codigo" placeholder="Ingrese su código">

            <button id="btnLogin" class="btn btn-primary w-100 py-2 fw-semibold">
                Validar acceso
            </button>
        </form>

        <p class="text-center text-muted mt-4 mb-0" style="font-size: 13px;">
            © {{ date('Y') }} Secretaría de Innovación y Digitalización
        </p>

    </div>
</div>

@endsection


@push('js')
<script>
/* ============================================================
   FORMATEO DE DUI
============================================================ */
document.getElementById("dui").addEventListener("input", function() {
    let v = this.value.replace(/\D/g, "").slice(0, 9);
    this.value = v.length >= 8 ? `${v.slice(0,8)}-${v.slice(8)}` : v;
});

/* ============================================================
   BOTÓN ANIMADO
============================================================ */
const btn = document.getElementById("btnLogin");

function activarAnimacionBoton() {
    btn.disabled = true;
    btn.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2"></span>
        Validando...
    `;
}

/* ============================================================
   VALIDACIONES SWEETALERT
============================================================ */
document.getElementById("loginForm").addEventListener("submit", function(e) {

    e.preventDefault();

    let dui     = document.getElementById("dui").value.trim();
    let codigo  = document.getElementById("codigo").value.trim();

    let errores = [];

    if (dui === "") {
        errores.push("El DUI es obligatorio.");
    } else if (!/^\d{8}-\d{1}$/.test(dui)) {
        errores.push("El formato del DUI es incorrecto (00000000-0).");
    }

    if (codigo === "") {
        errores.push("El código de marcador es obligatorio.");
    }

    if (errores.length > 0) {
        Swal.fire({
            icon: "error",
            title: "Validación incompleta",
            html: errores.join("<br>"),
            confirmButtonColor: "#d33"
        });
        return;
    }

    activarAnimacionBoton();
    this.submit();
});
</script>
@endpush
