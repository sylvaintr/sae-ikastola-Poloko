<x-app-layout>
    <script>
        const currentLang = "{{ app()->getLocale() }}";
    </script>

    @vite(['resources/js/actualite.js'])

    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <h2 class="fw-bold mb-0">{{ Lang::get('actualite.nouvelle_actualite', [], 'eus') }}
                @if (Lang::getLocale() == 'fr')
                    <p class="fw-light mb-0">{{ Lang::get('actualite.nouvelle_actualite') }}</p>
                @endif
            </h2>
            <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto">
                <a href="{{ route('admin.actualites.create') }}" class="btn btn-orange w-100 w-sm-auto">
                    {{ Lang::get('actualite.ajouter_une_actualite', [], 'eus') }}
                    @if (Lang::getLocale() == 'fr')
                        <span class="fw-light ms-2">{{ Lang::get('actualite.ajouter_une_actualite') }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.etiquettes.index') }}" class="btn btn-orange w-100 w-sm-auto">
                   {{ Lang::get('etiquette.gerer_les_etiquettes', [], 'eus') }}
                    @if (Lang::getLocale() == 'fr')
                        <span class="fw-light ms-2">{{ Lang::get('etiquette.gerer_les_etiquettes') }}</span>
                    @endif
                </a>
            </div>
        </div>

        {{-- Filters for DataTable --}}
        @php $etiquettes = \App\Models\Etiquette::all(); @endphp
        <div class="row mb-3 g-2">
            <div class="col-12 col-sm-6 col-md-3">
                <select id="filter-type" class="form-select">
                    <option value="">{{ __('actualite.all_types') ?? 'Tous les types' }}</option>
                    <option value="public">{{ __('actualite.public') }}</option>
                    <option value="private">{{ __('actualite.prive') }}</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <select id="filter-etat" class="form-select">
                    <option value="">{{ __('actualite.visibilite') }}</option>
                    <option value="active">{{ __('actualite.active')  }}</option>
                    <option value="archived">{{ __('actualite.archived') }}</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <select id="filter-etiquette" class="form-select">
                    <option value="">{{ __('etiquette.all')}}</option>
                    @foreach($etiquettes as $et)
                        <option value="{{ $et->idEtiquette }}">{{ $et->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-2 d-flex">
                <button id="reset-filters" class="btn btn-outline-secondary w-100 w-md-auto ms-md-auto">{{ __('actualite.reset') ?? 'Réinitialiser' }}</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="TableActualites" style="width:100%" data-ajax-url="{{ route('admin.actualites.data') }}">
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
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                afficherDataTable('TableActualites');
            });
        </script>
    </div>
</x-app-layout>
