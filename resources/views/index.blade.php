<x-app-layout>
    <div class="container py-4">
        <h1 class="text-3xl font-bold mb-6 text-center">{{Str::ucfirst(__('nav.actualites'))}}</h1>

        <x-actualites-index :actualites="$actualites" />

        @can('access-gestion-actualite')

        @endcan
    </div>
</x-app-layout>
