<x-app-layout>
    <div class="container py-4">
        <div>
            <h1 class="text-capitalize mb-0">{{ Lang::get('nav.factures', [], 'eus') }}</h1>
            @if (Lang::getLocale() == 'fr')
                <p class="text-capitalize">{{ Lang::get('nav.factures') }}</p>
            @endif
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
