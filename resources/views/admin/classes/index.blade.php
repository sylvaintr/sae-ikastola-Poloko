<x-app-layout>
    <div class="container py-4 demande-page">

        @php
            $applyLabel = \Illuminate\Support\Facades\Lang::has('actualite.apply', app()->getLocale())
                ? __('actualite.apply')
                : __('demandes.filters.submit');
            $resetLabel = \Illuminate\Support\Facades\Lang::has('actualite.reset', app()->getLocale())
                ? __('actualite.reset')
                : __('demandes.filters.reset');
            $placeholderSearch = \Illuminate\Support\Facades\Lang::has('classes.search_placeholder', app()->getLocale())
                ? __('classes.search_placeholder')
                : 'Rechercher une classe';
            $allLevelsLabel = \Illuminate\Support\Facades\Lang::has('classes.all_levels', app()->getLocale())
                ? __('classes.all_levels')
                : 'Tous les niveaux';
        @endphp

        {{-- Titre / sous-titre --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
            <div>
                <h1 class="text-capitalize mb-0">
                    {{ Lang::get('classes.title', [], 'eus') }}
                </h1>
                @if (Lang::getLocale() == 'fr')
                    <p class="text-capitalize mb-0 text-muted">
                        {{ Lang::get('classes.title') }}
                    </p>
                @endif
            </div>
            <div class="d-flex flex-nowrap gap-3 align-items-start">
                <div class="d-flex flex-column align-items-center">
                    <a href="{{ route('admin.classes.create') }}" class="btn demande-btn-primary text-white">
                        {{ Lang::get('classes.add', [], 'eus') }}
                    </a>
                    @if (Lang::getLocale() == 'fr')
                        <small class="text-muted mt-1">{{ Lang::get('classes.add') }}</small>
                    @endif
                </div>
            </div>
        </div>

        {{-- Filtres serveur --}}
        <form method="GET" action="{{ route('admin.classes.index') }}" class="row g-3 align-items-end admin-actualites-filters mb-3">
            <div class="col-sm-4">
                <label for="filter-classes-search" class="form-label fw-semibold">{{ __('classes.nom') }}</label>
                <input type="text" id="filter-classes-search" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="{{ $placeholderSearch }}">
            </div>
            <div class="col-sm-4">
                <label for="filter-classes-niveau" class="form-label fw-semibold">{{ __('classes.niveau') }}</label>
                <select id="filter-classes-niveau" name="niveau" class="form-select">
                    <option value="">{{ $allLevelsLabel }}</option>
                    @foreach($levels as $level)
                        <option value="{{ $level }}" @selected(($filters['niveau'] ?? '') === $level)>{{ $level }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4 d-flex gap-2 justify-content-end">
                <button type="submit" class="btn demande-btn-primary text-white">{{ $applyLabel }}</button>
                <a href="{{ route('admin.classes.index') }}" class="btn demande-btn-outline">{{ $resetLabel }}</a>
            </div>
        </form>

        {{-- Tableau --}}
        <div class="table-responsive">
            <table class="table align-middle demande-table mb-0">
                <thead>
                    <tr>
                        <th>
                            <div class="demande-header-label">
                                <span class="basque">{{ Lang::get('classes.nom', [], 'eus') }}</span>
                                <span class="fr">{{ Lang::get('classes.nom') }}</span>
                            </div>
                        </th>
                        <th>
                            <div class="demande-header-label">
                                <span class="basque">{{ Lang::get('classes.niveau', [], 'eus') }}</span>
                                <span class="fr">{{ Lang::get('classes.niveau') }}</span>
                            </div>
                        </th>
                        <th class="text-center">
                            <div class="demande-header-label">
                                <span class="basque">{{ Lang::get('classes.actions', [], 'eus') }}</span>
                                <span class="fr">{{ Lang::get('classes.actions') }}</span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classes as $classe)
                        <tr>
                            <td class="fw-semibold">{{ $classe->nom }}</td>
                            <td>{{ $classe->niveau }}</td>
                            <td class="text-center">
                                @include('admin.classes.partials.actions', ['classe' => $classe])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">{{ __('Aucune classe') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($classes instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-3 admin-pagination-container">
                {{ $classes->links() }}
            </div>
        @endif

    </div>
</x-app-layout>
