<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

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

    @stack('scripts')
</body>

</html>
