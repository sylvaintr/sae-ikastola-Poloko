<x-app-layout>
    <div class="container py-4 demande-page">

        {{-- Titre / sous-titre --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
            <div>
                <h1 class="text-capitalize mb-0">
                    {{ __('enfants.title', [], 'eus') }}
                </h1>
                @if (Lang::getLocale() == 'fr')
                    <p class="text-capitalize mb-0 text-muted">
                        {{ __('enfants.title') }}
                    </p>
                @endif
            </div>
            <div class="d-flex flex-nowrap gap-3 align-items-start">
                <div class="d-flex flex-column align-items-center">
                    <a href="{{ route('admin.enfants.create') }}" class="btn demande-btn-primary text-white">
                        {{ __('enfants.add', [], 'eus') }}
                    </a>
                    @if (Lang::getLocale() == 'fr')
                        <small class="text-muted mt-1">{{ __('enfants.add') }}</small>
                    @endif
                </div>
            </div>
        </div>

        {{-- Messages de succès --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Filtres serveur --}}
        <form method="GET" action="{{ route('admin.enfants.index') }}" class="row g-3 align-items-end admin-actualites-filters mb-3">
            <div class="col-sm-3">
                <label for="filter-enfants-search" class="form-label fw-semibold">{{ __('enfants.search') }}</label>
                <input type="text" id="filter-enfants-search" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('enfants.search_placeholder') }}">
            </div>
            <div class="col-sm-2">
                <label for="filter-enfants-sexe" class="form-label fw-semibold">{{ __('enfants.sexe') }}</label>
                <select id="filter-enfants-sexe" name="sexe" class="form-select">
                    <option value="">{{ __('enfants.all_sexes') }}</option>
                    <option value="M" @selected(($filters['sexe'] ?? '') === 'M')>{{ __('enfants.male') }}</option>
                    <option value="F" @selected(($filters['sexe'] ?? '') === 'F')>{{ __('enfants.female') }}</option>
                </select>
            </div>
            <div class="col-sm-3">
                <label for="filter-enfants-classe" class="form-label fw-semibold">{{ __('enfants.classe') }}</label>
                <select id="filter-enfants-classe" name="idClasse" class="form-select">
                    <option value="">{{ __('enfants.all_classes') }}</option>
                    @foreach($classes as $classe)
                        <option value="{{ $classe->idClasse }}" @selected(($filters['idClasse'] ?? '') == $classe->idClasse)>{{ $classe->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <label for="filter-enfants-famille" class="form-label fw-semibold">{{ __('enfants.famille') }}</label>
                <select id="filter-enfants-famille" name="idFamille" class="form-select">
                    <option value="">{{ __('enfants.all_families') }}</option>
                    <option value="null" @selected(($filters['idFamille'] ?? '') === 'null')>{{ __('enfants.no_family') }}</option>
                    @foreach($familles as $famille)
                        @php
                            $parents = $famille->utilisateurs;
                            $parentLabel = $parents->count() > 0 
                                ? $parents->first()->nom . ' ' . $parents->first()->prenom 
                                : '';
                        @endphp
                        <option value="{{ $famille->idFamille }}" @selected(($filters['idFamille'] ?? '') == $famille->idFamille)>
                            #{{ $famille->idFamille }} @if($parentLabel) - {{ $parentLabel }} @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2 d-flex gap-2 justify-content-end">
                <button type="submit" class="btn demande-btn-primary text-white">{{ __('enfants.filter') }}</button>
            </div>
        </form>

        {{-- Tableau --}}
        <div class="table-responsive">
            <table class="table align-middle demande-table mb-0">
                <thead>
                    <tr>
                        <th>{{ __('enfants.nom', [], 'eus') }} / {{ __('enfants.nom') }}</th>
                        <th>{{ __('enfants.prenom', [], 'eus') }} / {{ __('enfants.prenom') }}</th>
                        <th>{{ __('enfants.dateN', [], 'eus') }} / {{ __('enfants.dateN') }}</th>
                        <th>{{ __('enfants.sexe', [], 'eus') }} / {{ __('enfants.sexe') }}</th>
                        <th>{{ __('enfants.classe', [], 'eus') }} / {{ __('enfants.classe') }}</th>
                        <th>{{ __('enfants.famille', [], 'eus') }} / {{ __('enfants.famille') }}</th>
                        <th class="text-center">{{ __('enfants.actions', [], 'eus') }} / {{ __('enfants.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enfants as $enfant)
                        <tr>
                            <td class="fw-semibold">{{ $enfant->nom }}</td>
                            <td>{{ $enfant->prenom }}</td>
                            <td>{{ $enfant->dateN ? $enfant->dateN->format('d/m/Y') : '-' }}</td>
                            <td>{{ $enfant->sexe === 'M' ? __('enfants.male') : __('enfants.female') }}</td>
                            <td>{{ $enfant->classe ? $enfant->classe->nom : '-' }}</td>
                            <td>
                                @if($enfant->famille)
                                    #{{ $enfant->famille->idFamille }}
                                    @if($enfant->famille->utilisateurs->count() > 0)
                                        - {{ $enfant->famille->utilisateurs->first()->nom }} {{ $enfant->famille->utilisateurs->first()->prenom }}
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('admin.enfants.show', $enfant->idEnfant) }}" class="text-dark" title="{{ __('enfants.view') }}">
                                        <i class="bi bi-eye fs-5"></i>
                                    </a>
                                    <a href="{{ route('admin.enfants.edit', $enfant->idEnfant) }}" class="text-dark" title="{{ __('enfants.edit') }}">
                                        <i class="bi bi-pencil-square fs-5"></i>
                                    </a>
                                    <form action="{{ route('admin.enfants.destroy', $enfant->idEnfant) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('enfants.confirm_delete') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="border-0 bg-transparent text-dark p-0" title="{{ __('enfants.delete') }}">
                                            <i class="bi bi-trash fs-5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">{{ __('enfants.no_children') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $enfants->links() }}
        </div>
    </div>
</x-app-layout>

