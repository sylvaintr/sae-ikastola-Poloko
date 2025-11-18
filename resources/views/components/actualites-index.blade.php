<div class="row">
    @if($actualites->isEmpty())
    <p class="text-center text-gray-500">{{Str::ucfirst(__('aucune_actualite'))}}</p>
    @else
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 my-4">
        @foreach($actualites as $actu)
            @if (($actu->type == 'Publique')||auth()->check())
            <div class="bg-white shadow rounded p-4 my-3">
                <h2 class="h4 fw-bold mb-4">{{ $actu->titre }}</h2>
                <p class="text-gray-700 mb-3">{{ $actu->description }}</p>
                <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($actu->dateP)->format('d/m/Y') }}</p>
                @if(auth()->check())
                <p class="text-gray-700 mb-3">Type : {{ $actu->type }}</p>
                @endif
                <div class="d-flex justify-content-end mb-3">
                    <a class="btn-ikastola" href="{{ route('actualite-show', $actu) }}">En savoir plus...</a>
                </div>
            </div>
            @endif
        @endforeach
    </div>
    @endif
    @can('access-gestion-actualite')
    <div class="text-center mt-2">
        <a class="btn-ikastola" href="{{ route('admin.actualites.create') }}">Ajouter une publication</a>
    </div>
    @endcan
</div>
