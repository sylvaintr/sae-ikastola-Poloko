<x-app-layout>
    <div class="container py-4">
		<h2 class="fw-bold fs-3 text-dark mb-4">{{ $actualite->titre }}</h2>
        <h5 class="text-gray-700 mb-3">{{ $actualite->description }}</h5>
        <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($actualite->dateP)->format('d/m/Y') }}</p>
        <p class="text-gray-700 mb-3">Type : {{ $actualite->type }}</p>
        <p class="mb-3">{{ $actualite->contenu }}</p>
        <p class="text-gray-700 mb-3">{{ $actualite->archive ? "Publication archivée" : ""}}</p>

        <div class="mt-4">
            <button class="btn btn-secondary" onclick="history.back();">Retour</a>
        </div>
    </div>
</x-app-layout>
