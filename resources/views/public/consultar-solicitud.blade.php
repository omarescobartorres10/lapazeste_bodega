@extends('layouts.public')

@section('title', 'Consultar Solicitud')

@section('content')

<div class="container py-4" style="max-width:600px;">

    {{-- ALERTA SI NO SE ENCONTRÓ LA SOLICITUD --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-x-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    {{-- ALERTA DE ÉXITO (OPCIONAL) --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-warning text-dark fw-bold">
            <i class="bi bi-search me-2"></i> Consultar Estado de Solicitud
        </div>

        <div class="card-body p-4">

            <form method="POST" action="{{ route('solicitud.buscar') }}">
                @csrf

                <label class="form-label fw-semibold">Ingrese el código de solicitud</label>

                <input 
                    type="text" 
                    name="codigo" 
                    class="form-control mb-1"
                    placeholder="Ejemplo: SOL-00001"
                    value="{{ old('codigo') }}"
                    required
                >

                {{-- MENSAJE DE ERROR DE VALIDACIÓN --}}
                @error('codigo')
                    <div class="text-danger small mb-2">
                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                    </div>
                @enderror

                <div class="d-flex justify-content-between mt-3">

                    {{-- BOTÓN REGRESAR --}}
                    <a href="{{ route('solicitud.form') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle"></i> Regresar
                    </a>

                    {{-- BOTÓN CONSULTAR --}}
                    <button class="btn btn-warning px-4">
                        <i class="bi bi-search"></i> Consultar
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>

@endsection
