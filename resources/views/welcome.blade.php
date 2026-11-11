<!DOCTYPE html>
<html lang="{{ str_replace('_', '_', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="La Paz Este" />
    <meta name="author" content="La Paz Este" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('assets/form-guard.js') }}" defer></script>

    <meta http-equiv="Cache-Control" content="no-store" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    
    <title>ALCALDIA LA PAZ ESTE</title>

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="shortcut icon" href="{{ asset('img/LAPAZESTE.png') }}" type="image/x-icon">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom styles for this template-->
    <link href="{{ asset('assets/styleIndex.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/styleIndex1.css') }}">
</head>

<body id="page-top" class="warehouse-ui">
    @include('components.session-monitor')

    <!-- Page Wrapper -->
    <div id="wrapper">

        <x-navigation-menu />
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <x-navigation-header />
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    @yield('content')  <!-- Aquí se mostrará el contenido de la vista hija -->
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <x-footer />
            </footer>
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    @stack('js')
    <!-- Carga jQuery -->
    

    <!-- Bootstrap Bundle (incluye Popper para dropdowns) -->
    

    <link href="{{ asset('assets/warehouse-ui.css') }}?v={{ filemtime(public_path('assets/warehouse-ui.css')) }}" rel="stylesheet">
</body>

</html>
