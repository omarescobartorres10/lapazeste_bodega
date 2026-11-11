<!DOCTYPE html>
<html lang="{{ str_replace('_', '_', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="description" content="La Paz Este" />
    <meta name="author" content="La Paz Este" />

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta http-equiv="Cache-Control" content="no-store" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />

    <title>ALCALDÍA LA PAZ ESTE</title>

    <!-- FontAwesome -->
    <link rel="stylesheet" 
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Bootstrap -->
    <link rel="stylesheet" 
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('img/LAPAZESTE.png') }}" type="image/x-icon">

    <!-- Custom styles -->
    <link href="{{ asset('assets/styleIndex.css') }}" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body id="page-top">

    <div id="wrapper">

        <!-- Sidebar -->
        <x-navigation-menu />

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

                <!-- Topbar -->
                <x-navigation-header />

                <!-- Page Content -->
                <div class="container-fluid">
                    @yield('content')
                </div>

            </div>

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <x-footer />
            </footer>

        </div>

    </div>

    <!-- JS GLOBAL -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Scripts agregados desde las vistas -->
    @stack('js')

</body>
</html>
