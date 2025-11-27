<x-app-layout>
    <div class="container py-5">
        <div class="d-flex justify-content-between mb-4">
            <h3>Modifier l'Actualité #{{ $actualite->idActualite }}</h3>
            <a href="{{ route('admin.actualites.index') }}" class="btn btn-secondary">Retour</a>
        </div>

        <form action="{{ route('actualites.update', $actualite->idActualite) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                {{-- GAUCHE : CONTENU --}}
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white p-0">
                            <ul class="nav nav-tabs card-header-tabs m-0" id="editTab">
                                <li class="nav-item">
                                    <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#fr"
                                        type="button">🇫🇷 Français</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link fw-bold text-success" data-bs-toggle="tab"
                                        data-bs-target="#eus" type="button">Euskara</button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                {{-- FR --}}
                                <div class="tab-pane fade show active" id="fr">
                                    <div class="mb-3">
                                        <label for="titrefr">Titre (FR)</label>
                                        <input id="titrefr" type="text" name="titrefr" class="form-control"
                                            value="{{ $actualite->titrefr }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="descriptionfr">Description (FR)</label>
                                        <textarea id="descriptionfr" name="descriptionfr" class="form-control" rows="2">{{ $actualite->descriptionfr }}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="contenufr">Contenu (FR)</label>
                                        <textarea id="contenufr" name="contenufr" class="form-control" rows="6">{{ $actualite->contenufr }}</textarea>
                                    </div>
                                </div>

                                {{-- EU --}}
                                <div class="tab-pane fade" id="eus">
                                    <div class="mb-3">
                                        <label for="titreeus">Titre (EU)</label>
                                        <input id="titreeus" type="text" name="titreeus" class="form-control"
                                            value="{{ $actualite->titreeus }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="descriptioneus">Description (EU)</label>
                                        <textarea id="descriptioneus" name="descriptioneus" class="form-control" rows="2">{{ $actualite->descriptioneus }}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="contenueus">Contenu (EU)</label>
                                        <textarea id="contenueus" name="contenueus" class="form-control" rows="6">{{ $actualite->contenueus }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DROITE : IMAGES & OPTIONS --}}
                <div class="col-lg-4">
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <h5 class="fw-bold">Options</h5>
                            <div class="mb-3">
                                <label for="dateP">Date</label>
                                <input id="dateP" type="date" name="dateP" class="form-control"
                                    value="{{ $actualite->dateP->format('Y-m-d') }}">
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" name="archive" id="arch"
                                    {{ $actualite->archive ? 'checked' : '' }}>
                                <label class="form-check-label" for="arch">Archiver</label>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Sauvegarder les modifications</button>
                        </div>
                    </div>

                    {{-- GESTION IMAGES --}}
                    <div class="card shadow-sm bg-warning bg-opacity-10 border-warning">
                        <div class="card-header bg-warning text-dark fw-bold">Gérer les images</div>
                        <div class="card-body">
                            <div class="row g-2 mb-3">
                                @foreach ($actualite->documents as $doc)
                                    <div class="col-6 text-center">
                                        <img src="{{ asset('storage/' . $doc->chemin) }}" class="img-thumbnail mb-1"
                                            style="height: 80px; object-fit: cover;" alt="{{ $doc->nom }}">
                                        {{-- Bouton suppression (formulaire externe) --}}
                                        <button type="submit" form="del-img-{{ $doc->idDocument }}"
                                            class="btn btn-danger btn-sm py-0 w-100" style="font-size: 0.8rem;">
                                            Supprimer
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            <label for="images" class="small fw-bold">Ajouter des images :</label>
                            <input id="images" type="file" name="images[]"
                                class="form-control form-control-sm" multiple>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- Formulaires cachés pour la suppression des images --}}
        @foreach ($actualite->documents as $doc)
            <form id="del-img-{{ $doc->idDocument }}"
                action="{{ route('actualites.detachDocument', ['idActualite' => $actualite->idActualite, 'idDocument' => $doc->idDocument]) }}"
                method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    </div>
</x-app-layout>
