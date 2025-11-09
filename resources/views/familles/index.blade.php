<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Liste des familles
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        @foreach($familles as $famille)
            <div class="border rounded-lg p-4 mb-4 bg-white shadow">
                <p class="font-bold">Famille ID: {{ $famille->idFamille }}</p>

                <p class="font-semibold mt-2">Enfants :</p>
                @if($famille->enfants->count() > 0)
                    <ul class="pl-5 list-disc">
                        @foreach($famille->enfants as $enfant)
                            <li>{{ $enfant->prenom }} {{ $enfant->nom }} (Sexe: {{ $enfant->sexe }}, NNI: {{ $enfant->NNI }})</li>
                        @endforeach
                    </ul>
                @else
                    <p class="italic ml-4">Aucun enfant</p>
                @endif

                <p class="font-semibold mt-2">Utilisateurs :</p>
                @if($famille->utilisateurs->count() > 0)
                    <ul class="pl-5 list-disc">
                        @foreach($famille->utilisateurs as $user)
                            <li>{{ $user->prenom }} {{ $user->nom }} (Parité: {{ $user->pivot->parite ?? 'N/A' }})</li>
                        @endforeach
                    </ul>
                @else
                    <p class="italic ml-4">Aucun utilisateur</p>
                @endif
            </div>
        @endforeach
    </div>
</x-app-layout>

