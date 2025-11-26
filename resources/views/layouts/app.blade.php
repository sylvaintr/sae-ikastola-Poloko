<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>

    <!-- Datatable -->

    <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>

    <!-- Scripts -->

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="font-sans antialiased">
    <x-loader />


    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    @isset($header)
                        {{ $header }}
                    @endisset
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            <!-- SUCCES -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show my-3" role="alert">
                    <strong>Succès :</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            @endif

            <!-- ERREURS -->
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show my-3" role="alert">
                    <strong>Erreur :</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            @endif

            <!-- WARNINGS -->
            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show my-3" role="alert">
                    <strong>Attention :</strong> {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show my-3" role="alert">
                    <strong>Erreurs de validation :</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            @endif

            @yield('content')

            @isset($slot)
                {{ $slot }}
            @endisset
        </main>
    </div>



    @if (session('success'))
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
            <div id="successToast" class="toast align-items-center text-bg-success" role="alert" aria-live="assertive"
                aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ Lang::get(session('success'), [], 'eus') }}
                        @if (Lang::getLocale() == 'fr')
                            <p class="fw-light">{{ __(session('success')) }}</p>
                        @endif
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var toastEl = document.getElementById('successToast');
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
            });
        </script>
    @endif


    @if (session('error'))
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
            <div id="errorToast" class="toast align-items-center text-bg-danger" role="alert" aria-live="assertive"
                aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ Lang::get(session('error'), [], 'eus') }}
                        @if (Lang::getLocale() == 'fr')
                            <p class="fw-light">{{ __(session('error')) }}</p>
                        @endif
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var toastEl = document.getElementById('errorToast');
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
            });
        </script>
    @endif
    </div>
</body>

<!-- Bootstrap JS bundle (includes Popper) - ensures Bootstrap components (dropdowns, modals) work -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity=""
    crossorigin="anonymous"></script>

@stack('scripts')

</html>
