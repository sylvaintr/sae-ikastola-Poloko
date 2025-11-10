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
        <div class="container-fluid mx-auto px-4 py-4">
            <div class="card-header">
                <h2>Nouvelle actualité</h2>
            </div>
            <div class="card-body">
                <form action="{{ route('actualites.store')}}" method="post">
                    @csrf
                    <div class="mb-3">
                        <label for="titre" class="form-label">Titre</label>
                        <input id="titre" name="titre" type="text" class="form-control" placeholder="Titre"/>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" rows="3" cols="50" class="form-control" placeholder="Description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select name="type" id="type" class="form-select">
                            <option value="Privée">Privée</option>
                            <option value="Publique">Publique</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="idUtilisateur" class="form-label">Utilisateur</label>
                        <select name="idUtilisateur" id="idUtilisateur" class="form-select">
                            <option value="">-- Sélectionner un utilisateur --</option>
                            @foreach ($utilisateurs as $utilisateur)
                                <option value="{{ $utilisateur->idUtilisateur }}">
                                    {{ $utilisateur->nom }} {{ $utilisateur->prenom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="archive" value="0">
                    <div class="mb-3 form-check">
                        <input type="checkbox" id="archive" name="archive" value="1" class="form-check-input">
                        <label for="archive" class="form-check-label">Archiver</label>
                    </div>
                    <div class="mb-3">
                        <label for="lien" class="form-label">Lien (optionnel)</label>
                        <textarea id="lien" name="lien" row="2" cols="50" class="form-control" placeholder="Lien"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary fw-bold text-center">Ajouter</button>
                </form>
            </div>
        </div>
</div>
@endsection
