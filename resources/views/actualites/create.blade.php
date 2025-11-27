<x-app-layout>
    <div class="container py-5">
        <h2 class="mb-4 fw-bold text-center">Nouvelle Actualité Bilingue</h2>

        <form action="{{ route('actualites.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">
                {{-- COLONNE GAUCHE : CONTENU LINGUISTIQUE --}}
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white p-0">
                            {{-- ONGLETS DE LANGUE --}}
                            <ul class="nav nav-tabs card-header-tabs m-0" id="langTab">
                                <li class="nav-item">
                                    <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#fr"
                                        type="button">
                                        🇫🇷 Français
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link fw-bold text-success" data-bs-toggle="tab"
                                        data-bs-target="#eus" type="button">
                                        Euskara (Basque)
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                {{-- CONTENU FRANÇAIS --}}
                                <div class="tab-pane fade show active" id="fr">
                                    <div class="mb-3">
                                        <label for="titrefr" class="form-label">Titre (FR)</label>
                                        <input type="text" id="titrefr" name="titrefr" class="form-control"
                                            maxlength="30" placeholder="Titre en français" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="descriptionfr" class="form-label">Description courte (FR)</label>
                                        <textarea id="descriptionfr" name="descriptionfr" class="form-control" maxlength="100" rows="2" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="contenufr" class="form-label">Contenu Complet (FR)</label>
                                        <textarea id="contenufr" name="contenufr" class="form-control" rows="6" required></textarea>
                                    </div>
                                </div>

                                {{-- CONTENU BASQUE --}}
                                <div class="tab-pane fade" id="eus">
                                    <div class="mb-3">
                                        <label for="titreeus" class="form-label">Titre (EU)</label>
                                        <input type="text" id="titreeus" name="titreeus" class="form-control"
                                            maxlength="30" placeholder="Izenburua euskaraz" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="descriptioneus" class="form-label">Description courte (EU)</label>
                                        <textarea id="descriptioneus" name="descriptioneus" class="form-control" maxlength="100" rows="2" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="contenueus" class="form-label">Contenu Complet (EU)</label>
                                        <textarea id="contenueus" name="contenueus" class="form-control" rows="6" required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- COLONNE DROITE : INFOS COMMUNES --}}
                <div class="col-lg-4">
                    <div class="card shadow-sm bg-light border-0">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3">Paramètres Généraux</h5>

                            <div class="mb-3">
                                <label for="type" class="form-label fw-bold">Type</label>
                                <select id="type" name="type" class="form-select">
                                    <option value="public">Public</option>
                                    <option value="private">Privé</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="dateP" class="form-label fw-bold">Date de Publication</label>
                                <input type="date" id="dateP" name="dateP" class="form-control"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="lien" class="form-label fw-bold">Lien externe</label>
                                <input type="url" id="lien" name="lien" class="form-control"
                                    placeholder="https://...">
                            </div>

                            <div class="mb-3">
                                <label for="etiquettes" class="form-label fw-bold">Étiquettes</label>
                                <select id="etiquettes" name="etiquettes[]" class="form-select" multiple
                                    size="3">
                                    @foreach ($etiquettes as $tag)
                                        <option value="{{ $tag->idEtiquette }}">{{ $tag->nom }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="images" class="form-label fw-bold">Images</label>
                                <input type="file" id="images" name="images[]" class="form-control" multiple
                                    accept="image/*">
                                <small class="text-muted">Ces images s'afficheront pour les deux langues.</small>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary py-2">Publier l'Actualité</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
