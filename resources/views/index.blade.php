@extends('layouts.app')

@section('content')
<div class="container-fluid mx-auto px-4 py-4">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <h1 class="text-3xl font-bold mb-6 text-center">{{Str::ucfirst(__('nav.actualites'))}}</h1>

    <x-actualites-index :actualites="$actualites" />

    @can('access-gestion-actualite')

    @endcan
</div>
@endsection
