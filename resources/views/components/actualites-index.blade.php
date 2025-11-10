@php
    // Vérifier si l'utilisateur peut gérer les actualités
    $permissionActualies = auth()->check() && Auth::user()->can('access-gestion-actualite'); 
@endphp

<div class="row">
    @if($actualites->isEmpty())
    <p class="text-center text-gray-500">{{Str::ucfirst(__('aucune_actualite'))}}</p>
    @else
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 my-4">
        @foreach($actualites as $actu)
            @if (($actu->type == 'Publique')||$permissionActualies)
            <div class="bg-white rounded-2xl shadow p-4 my-3">
                <h2 class="h4 fw-bold mb-4">{{ $actu->titre }}</h2>
                <p class="text-gray-700 mb-3">{{ $actu->description }}</p>
                <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($actu->dateP)->format('d/m/Y') }}</p>
                @if($permissionActualies)
                <p class="text-gray-700 mb-3">Type : {{ $actu->type }}</p>
                <div>
                    <a class="btn btn-primary fw-bold" href="{{ route('actualites.edit') }}">Modifier</a>
                    <a class="btn btn-danger fw-bold" href="{{ route('actualites.delete') }}">Supprimer</a>
                </div>
                @endif
            </div>
            @endif
        @endforeach
    </div>
    @endif
    @if($permissionActualies)
    <div class="text-center mt-2">
        <a class="btn btn-success fw-bold" href="{{ route('actualites.create') }}">Ajouter une actualité</a>
    </div>
    @endif
</div>
