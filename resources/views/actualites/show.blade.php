<x-app-layout>
    @vite(['resources/js/actualite.js'])


    <style>
        /* --- Styles Typographiques --- */
        .actu-title-main {
            font-weight: 700;
            color: #212529;
            line-height: 1.2;
        }

        .actu-date {
            color: #6c757d;
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }

        .actu-body-primary {
            color: #212529;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
            text-align: justify;
        }

        .actu-body-secondary {
            color: #999;
            font-size: 1rem;
            line-height: 1.6;
            text-align: justify;
        }

        /* --- Styles Images --- */
        .main-image {
            border-radius: 15px;
            width: 100%;
            height: auto;
            max-height: 400px;
            /* Limite la hauteur pour ne pas pousser le texte trop bas */
            object-fit: cover;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .carousel-container-rounded {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .carousel-item img {
            height: 500px;
            /* Carousel plus grand pour la galerie du bas */
            object-fit: cover;
        }
    </style>

    <div class="container py-5">

        {{-- Bouton Retour --}}
        <div class="mb-4">
            <a href="{{ route('actualites.index') }}" class="text-decoration-none text-secondary hover-underline">
                <i class="bi bi-arrow-left"></i> {{ Lang::get('actualite.retour_aux_actualites', [], 'eus') }} @if(Lang::getLocale() == 'fr') / {{ __('actualite.retour_aux_actualites') }} @endif
            </a>
        </div>

        @php
            // Récupération des images
            $allImages = $actualite->documents->where('type', 'image');
            // La première image sert d'image principale
            $mainImage = $allImages->first();
            // Les autres images (à partir de la 2ème) vont dans le carousel
            $galleryImages = $allImages->skip(1);
        @endphp

        {{-- PARTIE HAUTE : TEXTE + IMAGE PRINCIPALE --}}
        <div class="row gx-5">

            {{-- COLONNE GAUCHE : TEXTES --}}
            <div class="col-lg-7 order-2 order-lg-1">

                {{-- Titres --}}
                <h1 class="mb-2">
                    <span class="actu-title-main d-block">{{ $actualite->titreeus }}</span>
                    @if (Lang::getLocale() == 'fr')
                        <span class="fw-light text-secondary fs-4 d-block">{{ $actualite->titrefr }}</span>
                    @endif
                </h1>

                {{-- Date --}}
                <p class="actu-date">
                    {{ __('actualite.publie_le') }} {{ $actualite->dateP->format('d/m/Y') }}
                    @if ($actualite->type)
                        <span class="badge bg-warning text-dark ms-2">{{ $actualite->type }}</span>
                    @endif
                </p>

                {{-- Contenu Basque --}}
                <div class="actu-body-primary" id="contenu-basque">


                </div>

                {{-- Contenu Français --}}
                @if (Lang::getLocale() == 'fr')
                    <div class="actu-body-secondary" id="contenu-francais"></div>
                @endif

                {{-- Lien Externe --}}
                @if ($actualite->lien)
                    <div class="mt-4 pt-3">
                        <a href="{{ $actualite->lien }}" target="_blank" class="btn btn-outline-dark">
                            Voir le lien associé <i class="bi bi-box-arrow-up-right ms-1"></i>
                        </a>
                    </div>
                @endif
            </div>

            {{-- COLONNE DROITE : IMAGE PRINCIPALE --}}
            {{-- Sur mobile (order-1), l'image s'affiche AVANT le texte. Sur PC (order-lg-2), elle est à droite --}}
            <div class="col-lg-5 mb-4 mb-lg-0 order-1 order-lg-2">
                @if ($mainImage)
                    <div class="sticky-top" style="top: 20px; z-index: 1;">
                        <img src="{{ asset('storage/' . $mainImage->chemin) }}" class="main-image" alt="premiere">
                        <p class="text-center text-muted small mt-2 fst-italic">
                            {{ $mainImage->nom }}
                        </p>
                    </div>
                @else
                    {{-- Placeholder si aucune image --}}
                    <div class="bg-light rounded-4 d-flex align-items-center justify-content-center text-muted"
                        style="height: 300px;">
                        Pas d'image principale
                    </div>
                @endif
            </div>
        </div>

        {{-- PARTIE BASSE : CAROUSEL (GALERIE) --}}
        {{-- On affiche cette section uniquement s'il reste des images --}}
        @if ($galleryImages->count() > 0)
            <div class="row mt-5 pt-5 border-top">
                <div class="col-12">
                    <h3 class="fw-bold mb-4">Galerie Photos / Argazki Galeria</h3>
                </div>

                {{-- On centre le carousel et on limite sa largeur pour l'esthétique --}}
                <div class="col-lg-10 mx-auto">
                    <div id="carouselGalerie" class="carousel slide carousel-fade carousel-container-rounded"
                        data-bs-ride="carousel">

                        {{-- Indicateurs (petits traits en bas) --}}
                        <div class="carousel-indicators">
                            @foreach ($galleryImages as $key => $img)
                                <button type="button" data-bs-target="#carouselGalerie"
                                    data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}"
                                    aria-current="true"></button>
                            @endforeach
                        </div>

                        <div class="carousel-inner">
                            @foreach ($galleryImages as $image)
                                <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                    <img src="{{ asset('storage/' . $image->chemin) }}" class="d-block w-100"
                                        alt="Galerie">
                                </div>
                            @endforeach
                        </div>

                        {{-- Contrôles Suivant/Précédent --}}
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselGalerie"
                            data-bs-slide="prev">
                            <span class="carousel-control-prev-icon bg-dark bg-opacity-50 rounded-circle p-3"
                                aria-hidden="true"></span>
                            <span class="visually-hidden">Précédent</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselGalerie"
                            data-bs-slide="next">
                            <span class="carousel-control-next-icon bg-dark bg-opacity-50 rounded-circle p-3"
                                aria-hidden="true"></span>
                            <span class="visually-hidden">Suivant</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AfficherMarkdownfromTexte(@json($actualite->contenueus), 'contenu-basque')
            @if (Lang::getLocale() == 'fr')
                
            AfficherMarkdownfromTexte(@json($actualite->contenufr), 'contenu-francais')
            @endif
        });
    </script>

</x-app-layout>
