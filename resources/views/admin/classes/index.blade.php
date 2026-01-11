<x-app-layout>
    <div class="container">

        {{-- Titre / sous-titre --}}
        <div>
            <h1 class="text-capitalize mb-0">
                {{ Lang::get('classes.title', [], 'eus') }}
            </h1>
            @if (Lang::getLocale() == 'fr')
                <p class="text-capitalize">
                    {{ Lang::get('classes.title') }}
                </p>
            @endif
        </div>

        {{-- Bouton ajouter une classe --}}
        <div class="mb-3 text-center text-md-end">
            <a href="{{ route('admin.classes.create') }}" class="btn btn-warning fw-semibold w-100 w-md-auto">
                {{ Lang::get('classes.add', [], 'eus') }}
            </a>
            @if (Lang::getLocale() == 'fr')
                <p class="fw-light mb-0">
                    {{ Lang::get('classes.add') }}
                </p>
            @endif
        </div>

        {{-- DataTable --}}
        <div class="table-responsive">
            <table id="TableClasses" class="table table-striped nowrap dt-left" style="width:100%">
                <thead>
                    <tr>
                        <th class="text-left">
                            {{ Lang::get('classes.nom', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr')
                                <p class="fw-light mb-0">
                                    {{ Lang::get('classes.nom') }}
                                </p>
                            @endif
                        </th>
                        <th class="text-left">
                            {{ Lang::get('classes.niveau', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr')
                                <p class="fw-light mb-0">
                                    {{ Lang::get('classes.niveau') }}
                                </p>
                            @endif
                        </th>
                        <th class="text-left">
                            {{ Lang::get('classes.actions', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr')
                                <p class="fw-light mb-0">
                                    {{ Lang::get('classes.actions') }}
                                </p>
                            @endif
                        </th>
                    </tr>
                </thead>
            </table>
        </div>

        {{-- Langue courante envoyée au JS --}}
        <script>
            const currentLang = "{{ app()->getLocale() }}";
        </script>

        {{-- JS DataTable spécifique aux classes --}}
        @vite(['resources/js/classes.js'])

    </div>
</x-app-layout>
