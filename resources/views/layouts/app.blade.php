<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendors/feather/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendors/mdi/css/materialdesignicons.min.css') }}">

    <!-- DataTables CSS -->
    {{-- <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendors/datatables.net-bs5/dataTables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/js/select.dataTables.min.css') }}"> --}}

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/css/admin.css') }}">

    <link rel="shortcut icon" href="{{ asset('dashboard_assets/assets/images/favicon.png') }}">
    <script>
        window.APP_URL = "{{ config('app.url') }}";
        window.CSRF_TOKEN = "{{ csrf_token() }}";
    </script>

</head>

<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper">
            @include('layouts.partials._sidebar')

            <div class="main-panel">
                <div class="content-wrapper">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>  


    {{-- <script src="{{ asset('js/admin/margin-rules/rules.js') }}"></script> --}}

    <!-- JS Vendors -->
    <script src="{{ asset('dashboard_assets/assets/vendors/js/vendor.bundle.base.js') }}"></script>

    <!-- Chart.js -->
    <script src="{{ asset('dashboard_assets/assets/vendors/chart.js/chart.umd.js') }}"></script>

    <!-- DataTables Scripts (Correct Order) -->
    {{-- <script src="{{ asset('dashboard_assets/assets/vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendors/datatables.net-bs5/dataTables.bootstrap5.js') }}"></script> --}}
    <script src="{{ asset('dashboard_assets/assets/js/dataTables.select.min.js') }}"></script>

    <!-- Core Template Scripts -->
    <script src="{{ asset('dashboard_assets/assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/js/template.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/js/settings.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/js/todolist.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/js/reservations.js') }}"></script>

    <!-- Custom JS -->
    <script src="{{ asset('dashboard_assets/assets/js/jquery.cookie.js') }}" type="text/javascript"></script>

    <!-- IMPORTANT: Ensure DOM Ready in dashboard.js -->
    <script src="{{ asset('dashboard_assets/assets/js/dashboard.js') }}"></script>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
