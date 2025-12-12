<x-app-layout>
    <div class="container py-4">

        {{-- Header : titre + retour --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 fw-bold mb-1">
                    {{ __('classes.title', [], 'eus') }}
                </h1>

                @if (Lang::getLocale() == 'fr')
                    <p class="text-muted small mb-0">
                        {{ __('classes.title') }}
                    </p>
                @endif
            </div>

            <div class="text-end">
                <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary btn-sm">
                    ← {{ __('classes.back_to_list', [], 'eus') }}
                    @if (Lang::getLocale() == 'fr')
                        <span class="d-block small fw-light">
                            {{ __('classes.back_to_list') }}
                        </span>
                    @endif
                </a>
            </div>
        </div>

        {{-- Carte infos classe --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">

                <h2 class="h6 fw-semibold mb-3">
                    {{ __('classes.details', [], 'eus') }}
                    @if (Lang::getLocale() == 'fr')
                        <span class="d-block fw-light text-muted">
                            {{ __('classes.details') }}
                        </span>
                    @endif
                </h2>

                <div class="row small text-muted">

                    {{-- Nom --}}
                    <div class="col-md-4 mb-2">
                        <span class="d-block fw-semibold text-dark">
                            {{ __('classes.nom', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr')
                                <span class="fw-light">{{ __('classes.nom') }}</span>
                            @endif
                        </span>
                        <span class="fw-normal text-black">{{ $classe->nom }}</span>
                    </div>

                    {{-- Niveau --}}
                    <div class="col-md-4 mb-2">
                        <span class="d-block fw-semibold text-dark">
                            {{ __('classes.niveau', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr')
                                <span class="fw-light">{{ __('classes.niveau') }}</span>
                            @endif
                        </span>
                        <span class="fw-normal text-black">{{ $classe->niveau }}</span>
                    </div>

                    {{-- Nombre d'élèves --}}
                    <div class="col-md-4 mb-2">
                        <span class="d-block fw-semibold text-dark">
                            {{ __('classes.students_count', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr')
                                <span class="fw-light">{{ __('classes.students_count') }}</span>
                            @endif
                        </span>
                        <span class="fw-normal text-black">
                            {{ $classe->enfants->count() }}
                        </span>
                    </div>

                </div>

            </div>
        </div>

        {{-- Carte liste élèves --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">

                <h2 class="h6 fw-semibold mb-3 d-flex justify-content-between align-items-center">
                    <span>
                        {{ __('classes.children', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr')
                            <span class="d-block fw-light text-muted">
                                {{ __('classes.children') }}
                            </span>
                        @endif
                    </span>

                    <span class="badge bg-warning text-dark">
                        {{ trans_choice('classes.students_badge', $classe->enfants->count(), ['count' => $classe->enfants->count()]) }}
                    </span>

                </h2>

                @if ($classe->enfants->isEmpty())
                    <p class="text-muted small mb-0">
                        {{ __('classes.children_empty', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr')
                            <span class="d-block">
                                {{ __('classes.children_empty') }}
                            </span>
                        @endif
                    </p>
                @else
                    <div class="table-responsive small">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('classes.child_name', [], 'eus') }}</th>
                                    <th>{{ __('classes.child_birthdate', [], 'eus') }}</th>
                                    <th>{{ __('classes.child_gender', [], 'eus') }}</th>
                                    <th>{{ __('classes.child_nni', [], 'eus') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($classe->enfants as $index => $child)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $child->prenom }} {{ $child->nom }}</td>
                                        <td>
                                            @if ($child->dateN)
                                                {{ $child->dateN->format('d/m/Y') }}
                                            @endif
                                        </td>
                                        <td>{{ $child->sexe }}</td>
                                        <td>{{ $child->NNI }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
