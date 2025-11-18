@extends('layouts.app')

@section('content')
<div class="container-fluid mx-auto px-4 py-4">
    <h1 class="text-3xl font-bold mb-6 text-center">Modifier l'actualité</h1>

    @include('admin.actualites.partials.form', ['actualite' => $actualite, 'utilisateurs' => $utilisateurs])
</div>
@endsection
