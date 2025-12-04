<x-app-layout>
    {{-- Bloc de style pour coller à la maquette --}}
    <style>
        .actu-orange {
            color: #FF8C00;
            font-weight: bold;
            text-decoration: none;
            border-bottom: 2px solid #FF8C00;
        }

        .actu-orange:hover {
            color: #e07b00;
            border-color: #e07b00;
        }

        .actu-sublink {
            font-size: 0.85rem;
            color: #6c757d;
            display: block;
            margin-top: 2px;
            text-decoration: none;
        }

        .actu-image-container img {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .text-subtitle {
            color: #999;
            font-weight: 300;
            font-size: 0.9rem;
        }

        .actu-item {
            padding-bottom: 3rem;
        }
    </style>

    <div class="container py-5">
        <div class="mb-5">
            <h1 class="text-capitalize mb-0 fw-bold ">{{ Lang::get('nav.actualites', [], 'eus') }}</h1>
            @if (Lang::getLocale() == 'fr')
                <p class="text-capitalize">{{ Lang::get('nav.actualites') }}</p>
            @endif
            @can('gerer-actualites')
                <a class="mt-2 btn btn-warning mb-0" href="{{ route('admin.actualites.create') }}">
                    + {{ Lang::get('actualite.ajouter_une_actualite', [], 'eus') }}
                </a>
                    @if (Lang::getLocale() == 'fr')
                        </br>
                        <span class="fw-light"> {{ __('actualite.ajouter_une_actualite') }}</span>
                    @endif
            @endcan




<div class="dropdown d-flex flex-row-reverse">
    <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown"
        aria-expanded="false">
        {{ __('actualite.filter') }}
    </button>

    <ul class="dropdown-menu p-3" style="min-width: 250px;"> 
        <form action="{{ route('actualites.filter') }}" method="POST" id="filter-form">
            @csrf
            @foreach ($etiquettes as $etiquette)
                
                <li class="mb-2"> 
                    <div class="form-check"> 
                        <input type="checkbox" class="form-check-input etiquette-filter"
                            name="etiquettes[]" value="{{ $etiquette->idEtiquette }}" id="etiquette-{{ $etiquette->idEtiquette }}"
                            @if(!empty($selectedEtiquettes) && in_array($etiquette->idEtiquette, (array) $selectedEtiquettes)) checked @endif>
                        
                        
                        <label class="form-check-label" for="etiquette-{{ $etiquette->idEtiquette }}">
                            {{ $etiquette->nom }}
                        </label>
                    </div>
                </li>
            @endforeach
        </form>
    </ul>
</div>





        </div>

        <div class="row">
            <div class="col-12">

                @forelse($actualites as $actualite)
                    <div class="row mb-5 align-items-center actu-item">


                        <div class="col-md-7 pe-md-5 order-2 order-md-1">

                            <h2 class="fw-bold text-dark mb-3">
                                {{ $actualite->titreeus }}
                                @if (Lang::getLocale() == 'fr')
                                    </br> <span class="fw-light"> {{ $actualite->titrefr }}</span>
                                @endif
                            </h2>

                            <div class="text-muted mb-4">
                                {{ Str::limit($actualite->descriptioneus, 150) }}
                                @if (Lang::getLocale() == 'fr')
                                    </br> <span class="fw-light">
                                        {{ Str::limit($actualite->descriptionfr, 150) }}</span>
                                @endif
                            </div>

                            <a href="{{ route('actualites.show', $actualite->idActualite) }}"
                                class="text-start text-md-end text-decoration-none">
                                <div class="d-flex flex-column ">
                                    <span class="actu-orange fw-bold ms-md-auto me-auto me-md-0 ">
                                        {{ lang::get('actualite.voirPlus', [], 'eus') }}
                                    </span>
                                    @if (Lang::getLocale() == 'fr')
                                        <span class="actu-sublink">{{ __('actualite.voirPlus') }}</span>
                                    @endif
                                </div>
                            </a>
                        </div>

                        <div class="col-md-5 mb-4 mb-md-0 actu-image-container order-1 order-md-2">
                            @php
                                $image = $actualite->documents->where('type', 'image')->first();
                            @endphp

                            @if ($image)
                                <img src="{{ asset('storage/' . $image->chemin) }}" alt="{{ $actualite->titre }}">
                            @else
                                <div class="bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center rounded-4"
                                    style="height: 250px; border-radius: 15px;">
                                    <span class="text-muted">Pas d'image / Irudi gabe</span>
                                </div>
                            @endif
                        </div>

                    </div>
                @empty
                    <div class="alert alert-info">
                        Aucune actualité disponible pour le moment.
                    </div>
                @endforelse

                <div class="d-flex justify-content-center mt-4">
                    {{ $actualites->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('filter-form');
        if (!form) return;

        // Find the dropdown that contains our form
        var dropdown = form.closest('.dropdown');
        if (!dropdown) return;

        var initialChecked = new Set();

        function snapshotChecked() {
            initialChecked.clear();
            form.querySelectorAll('.etiquette-filter:checked').forEach(function (cb) {
                initialChecked.add(cb.value);
            });
        }

        function currentCheckedSet() {
            var s = new Set();
            form.querySelectorAll('.etiquette-filter:checked').forEach(function (cb) {
                s.add(cb.value);
            });
            return s;
        }

        // On open, take a snapshot of checked values
        dropdown.addEventListener('show.bs.dropdown', function () {
            snapshotChecked();
        });

        // On close, compare and submit only if changed
        dropdown.addEventListener('hidden.bs.dropdown', function () {
            var now = currentCheckedSet();
            var changed = false;
            if (now.size !== initialChecked.size) changed = true;
            else {
                now.forEach(function (v) { if (!initialChecked.has(v)) changed = true; });
            }
            if (changed) form.submit();
        });
    });
</script>
