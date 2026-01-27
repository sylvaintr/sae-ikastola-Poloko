<x-app-layout>

    <div class="container">
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

        @if (isset($fichierpdf))
            <iframe src="{{ $fichierpdf }}" title="facture" width="100%" height="600px"></iframe>
        @else
            {!! $inlinedHtml !!}
        @endif
 
    </div>

</x-app-layout>
