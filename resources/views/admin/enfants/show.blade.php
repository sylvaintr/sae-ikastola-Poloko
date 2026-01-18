<x-app-layout>
    <div class="container py-4">

        {{-- Header : titre + retour --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 fw-bold mb-1">
                    {{ __('enfants.title', [], 'eus') }}
                </h1>

                @if (Lang::getLocale() == 'fr')
                    <p class="text-muted small mb-0">
                        {{ __('enfants.title') }}
                    </p>
                @endif
            </div>

            <div class="text-end">
                <a href="{{ route('admin.enfants.index') }}" class="text-decoration-none fw-semibold text-warning">
                    ← {{ __('enfants.back_to_list', [], 'eus') }}
                    @if (Lang::getLocale() == 'fr')
                        <span class="d-block small fw-semibold text-warning">
                            {{ __('enfants.back_to_list') }}
                        </span>
                    @endif
                </a>
            </div>
        </div>

        {{-- Carte détails --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <h2 class="h6 fw-semibold mb-0">
                        {{ __('enfants.details', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr')
                            <span class="d-block fw-light text-muted">
                                {{ __('enfants.details') }}
                            </span>
                        @endif
                    </h2>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.enfants.edit', $enfant->idEnfant) }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil-square"></i> {{ __('enfants.edit') }}
                        </a>
                        <form action="{{ route('admin.enfants.destroy', $enfant->idEnfant) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('enfants.confirm_delete') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-trash"></i> {{ __('enfants.delete') }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>{{ __('enfants.nom', [], 'eus') }} / {{ __('enfants.nom') }}:</strong>
                        <p class="mb-0">{{ $enfant->nom }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>{{ __('enfants.prenom', [], 'eus') }} / {{ __('enfants.prenom') }}:</strong>
                        <p class="mb-0">{{ $enfant->prenom }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>{{ __('enfants.dateN', [], 'eus') }} / {{ __('enfants.dateN') }}:</strong>
                        <p class="mb-0">{{ $enfant->dateN ? $enfant->dateN->format('d/m/Y') : '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>{{ __('enfants.NNI', [], 'eus') }} / {{ __('enfants.NNI') }}:</strong>
                        <p class="mb-0">{{ $enfant->NNI }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>{{ __('enfants.classe', [], 'eus') }} / {{ __('enfants.classe') }}:</strong>
                        <p class="mb-0">
                            @if($enfant->classe)
                                <a href="{{ route('admin.classes.show', $enfant->classe->idClasse) }}">
                                    {{ $enfant->classe->nom }} ({{ $enfant->classe->niveau }})
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>{{ __('enfants.famille', [], 'eus') }} / {{ __('enfants.famille') }}:</strong>
                        <p class="mb-0">
                            @if($enfant->famille)
                                <a href="{{ route('admin.familles.show', $enfant->famille->idFamille) }}">
                                    #{{ $enfant->famille->idFamille }}
                                    @if($enfant->famille->utilisateurs->count() > 0)
                                        - {{ $enfant->famille->utilisateurs->first()->nom }} {{ $enfant->famille->utilisateurs->first()->prenom }}
                                    @endif
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

