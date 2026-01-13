<x-app-layout>
    <style>
        /* Cache les flèches de tri sur la ligne des filtres */
        .filters th {
            background-image: none !important;
            pointer-events: none;
            /* Empêche le tri accidentel */
        }

        /* Réactive les événements pour les inputs à l'intérieur */
        .filters th input,
        .filters th select {
            pointer-events: auto;
        }
    </style>
    <div class="container">
        <div>
            <h1 class="text-capitalize mb-0">{{ Lang::get('nav.factures', [], 'eus') }}</h1>
            @if (Lang::getLocale() == 'fr')
                <p class="text-capitalize">{{ Lang::get('nav.factures') }}</p>
            @endif
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="filtreEtat" class="form-label">
                    {{ Lang::get('facture.etat', [], 'eus') }}
                    @if (Lang::getLocale() == 'fr')
                        / {{ Lang::get('facture.etat') }}
                    @endif
                </label>

                <select id="filtreEtat" class="form-select">
                    
                    <option value="">
                        {{ Lang::get('facture.tous_les_etats', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr')
                            / {{ Lang::get('facture.tous_les_etats') }}
                        @endif
                    </option>
                    <option value="manuel">{{ __('facture.manuel') }}</option>
                    <option value="verifier">{{ __('facture.verifier') }}</option>
                    <option value="brouillon">{{ __('facture.brouillon') }}</option>
                </select>
            </div>
        </div>

        

        <div class="table-responsive">
            <table id="TableFacture" class="table table-striped nowrap dt-left" style="width:100%">
                <thead>
                    <tr>
                        <th class="text-left">{{ Lang::get('facture.id_facture', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr')
                                <p class="fw-light mb-0">{{ Lang::get('facture.id_facture') }}</p>
                            @endif
                        </th>
                        <th class="text-left">
                            {{ Lang::get('facture.titre', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr')
                                <p class="fw-light mb-0">{{ Lang::get('facture.titre') }}</p>
                            @endif
                        </th>
                        <th class="text-left">{{ Lang::get('facture.etat', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr')
                                <p class="fw-light mb-0">{{ Lang::get('facture.etat') }}</p>
                            @endif
                        </th>
                        <th class="text-left">{{ Lang::get('facture.id_famille', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr')
                                <p class="fw-light mb-0">{{ Lang::get('facture.id_famille') }}</p>
                            @endif
                        </th>
                        <th class="text-left">{{ Lang::get('facture.date_creation', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr')
                                <p class="fw-light mb-0">{{ Lang::get('facture.date_creation') }}</p>
                            @endif
                        </th>
                        <th class="text-left">{{ Lang::get('facture.actions', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr')
                                <p class="fw-light mb-0">{{ Lang::get('facture.actions') }}</p>
                            @endif
                        </th>
                    </tr>
                </thead>

            </table>
        </div>






        <script>
            const currentLang = "{{ app()->getLocale() }}";
        </script>

        @vite(['resources/js/facture.js'])

</x-app-layout>
