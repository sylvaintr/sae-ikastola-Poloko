@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-center">{{Str::ucfirst(__('nav.actualites'))}}</h1>

    <x-actualites-index :actualites="$actualites" />

    @can('access-gestion-actualite')
    <a href="{{ route('actualite.edit') }}">Modifier</a>
    @endcan
</div>
@endsection
