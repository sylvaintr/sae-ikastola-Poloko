<x-app-layout class="container">
    <a href="{{ route('admin.facture.index') }}" class="btn btn-secondary mb-4 my-3 ">
        <div class="row">

            <i class="bi bi-arrow-left col my-auto "></i>
            <div class="col ">
                <p class="text-capitalize mb-0 text-nowrap">
                    {{ Lang::get('facture.revenir_en_arriere', [], 'eus') }}
                </p>
                @if (Lang::getLocale() == 'fr')
                    <p class="text-capitalize text-nowrap mb-0">{{ Lang::get('facture.revenir_en_arriere') }}</p>
                @endif
            </div>
        </div>
    </a>
    <div class="container">

        @include('facture.template.facture-html')
    </div>

</x-app-layout>
