<x-app-layout>
    <script>
        const currentLang = "{{ app()->getLocale() }}";
    </script>

    @vite(['resources/js/actualite.js'])

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">{{ Lang::get('actualite.nouvelle_actualite', [], 'eus') }}
                @if (Lang::getLocale() == 'fr')
                    <p class="fw-light mb-0">{{ Lang::get('actualite.nouvelle_actualite') }}</p>
                @endif
            </h2>
            <div>
                <a href="{{ route('admin.actualites.create') }}" class="btn btn-orange">
                    <i class="bi bi-plus-circle"></i> {{ Lang::get('actualite.ajouter_une_actualite', [], 'eus') }}
                    @if (Lang::getLocale() == 'fr')
                        <span class="fw-light ms-2">{{ Lang::get('actualite.ajouter_une_actualite') }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.etiquettes.create') }}" class="btn btn-orange">
                    <i class="bi bi-plus-circle"></i> {{ Lang::get('etiquette.nouvelle', [], 'eus') }}
                    @if (Lang::getLocale() == 'fr')
                        <span class="fw-light ms-2">{{ Lang::get('etiquette.nouvelle') }}</span>
                    @endif
                </a>
            </div>
        </div>

        {{-- Filters for DataTable --}}
        @php $etiquettes = \App\Models\Etiquette::all(); @endphp
        <div class="row mb-3 g-2">
            <div class="col-sm-3">
                <select id="filter-type" class="form-select">
                    <option value="">{{ __('actualite.all_types') ?? 'Tous les types' }}</option>
                    <option value="public">{{ __('actualite.public') }}</option>
                    <option value="private">{{ __('actualite.prive') }}</option>
                </select>
            </div>
            <div class="col-sm-3">
                <select id="filter-etat" class="form-select">
                    <option value="">{{ __('actualite.all') ?? 'Tous' }}</option>
                    <option value="active">{{ __('actualite.active') ?? 'Active' }}</option>
                    <option value="archived">{{ __('actualite.archived') ?? 'Archivée' }}</option>
                </select>
            </div>
            <div class="col-sm-4">
                <select id="filter-etiquette" class="form-select">
                    <option value="">{{ __('etiquette.all') ?? 'Toutes les étiquettes' }}</option>
                    @foreach($etiquettes as $et)
                        <option value="{{ $et->idEtiquette }}">{{ $et->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2 d-flex">
                <button id="reset-filters" class="btn btn-outline-secondary ms-auto">{{ __('actualite.reset') ?? 'Réinitialiser' }}</button>
            </div>
        </div>

        <table class="table table-hover align-middle mb-0" id="TableActualites" style="width:100%">
            <thead class="bg-light">
                <tr>
                    <th>{{ Lang::get('actualite.titre', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr')
                            <p class="fw-light mb-0">{{ Lang::get('actualite.titre') }}</p>
                        @endif
                    </th>
                    <th>{{ Lang::get('actualite.type', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr')
                            <p class="fw-light mb-0">{{ Lang::get('actualite.type') }}</p>
                        @endif
                    </th>
                    <th>{{ Lang::get('actualite.date_publication', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr')
                            <p class="fw-light mb-0">{{ Lang::get('actualite.date_publication') }}</p>
                        @endif
                    </th>
                    <th>{{ Lang::get('facture.etat', [], 'eus') ?? Lang::get('actualite.etat', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr')
                            <p class="fw-light mb-0">{{ Lang::get('facture.etat') ?? Lang::get('actualite.etat') }}</p>
                        @endif
                    </th>
                    <th class="text-end pe-4">{{ Lang::get('facture.actions', [], 'eus') ?? Lang::get('actualite.actions', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr')
                            <p class="fw-light mb-0">{{ Lang::get('facture.actions') ?? Lang::get('actualite.actions') }}</p>
                        @endif
                    </th>
                </tr>
            </thead>
        </table>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                afficherDataTable('TableActualites');
            });
        </script>
    </div>
</x-app-layout>
