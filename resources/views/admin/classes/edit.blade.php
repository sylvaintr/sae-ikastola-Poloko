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

        {{-- Messages d'erreur --}}
        @if ($errors->any())
            <div class="alert alert-danger small">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $erreur)
                        <li>{{ $erreur }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Carte formulaire --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">

                <h2 class="h6 fw-semibold mb-3">
                    {{ __('classes.edit_title', [], 'eus') }}
                    @if (Lang::getLocale() == 'fr')
                        <span class="d-block fw-light text-muted">
                            {{ __('classes.edit_title') }}
                        </span>
                    @endif
                </h2>

                <form action="{{ route('admin.classes.update', $classe) }}" method="POST" class="small">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        {{-- Nom de la classe --}}
                        <div class="col-md-6 mb-3">
                            <label for="nom" class="form-label mb-1">
                                {{ __('classes.nom', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr')
                                    <span class="d-block text-muted fw-light">
                                        {{ __('classes.nom') }}
                                    </span>
                                @endif
                            </label>
                            <input type="text" id="nom" name="nom" value="{{ old('nom', $classe->nom) }}"
                                class="form-control @error('nom') is-invalid @enderror">
                            @error('nom')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Niveau --}}
                        <div class="col-md-6 mb-3">
                            <label for="niveau" class="form-label mb-1">
                                {{ __('classes.niveau', [], 'eus') }}
                                @if (Lang::getLocale() == 'fr')
                                    <span class="d-block text-muted fw-light">
                                        {{ __('classes.niveau') }}
                                    </span>
                                @endif
                            </label>

                            @if ($levels->isNotEmpty())
                                <select id="niveau" name="niveau"
                                    class="form-select @error('niveau') is-invalid @enderror">
                                    <option value="">
                                        {{ __('classes.niveau_select_placeholder', [], 'eus') }}
                                        @if (Lang::getLocale() == 'fr')
                                            - {{ __('classes.niveau_select_placeholder') }}
                                        @endif
                                    </option>

                                    @foreach ($levels as $level)
                                        <option value="{{ $level }}"
                                            {{ old('niveau', $classe->niveau) === $level ? 'selected' : '' }}>
                                            {{ $level }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" name="niveau" value="{{ old('niveau', $classe->niveau) }}"
                                    placeholder="{{ __('classes.niveau_placeholder', [], 'eus') }}"
                                    class="form-control @error('niveau') is-invalid @enderror">
                            @endif

                            @error('niveau')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                    </div>

                    {{-- Sélecteur d’enfants --}}
                    <div class="mb-3 border-top pt-3">
                        <div class="form-label fw-semibold mb-2">
                            {{ __('classes.children', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr')
                                <span class="d-block text-muted fw-light">
                                    {{ __('classes.children') }}
                                </span>
                            @endif
                        </div>

                        <div class="role-selector-container">
                            <div class="row g-3">
                                {{-- Liste des enfants disponibles --}}
                                <div class="col-md-6">
                                    <label for="child-search" class="form-label small mb-1">
                                        {{ __('classes.children_search', [], 'eus') }}
                                        @if (Lang::getLocale() == 'fr')
                                            <span class="d-block text-muted fw-light">
                                                {{ __('classes.children_search') }}
                                            </span>
                                        @endif
                                    </label>

                                    <input type="text" id="child-search" class="form-control"
                                        placeholder="{{ __('classes.children_search_placeholder', [], 'eus') }}">

                                    <div id="available-children" class="role-list mt-2">
                                        @foreach ($children as $child)
                                            <div class="role-item child-item" data-child-id="{{ $child->idEnfant }}"
                                                data-child-name="{{ $child->prenom }} {{ $child->nom }}">
                                                <span>{{ $child->prenom }} {{ $child->nom }}</span>
                                                <i class="bi bi-plus-circle"></i>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Liste des enfants sélectionnés --}}
                                <div class="col-md-6">
                                    <div class="form-label small mb-1">
                                        {{ __('classes.children_selected', [], 'eus') }} <span
                                            class="text-danger">*</span>
                                        @if (Lang::getLocale() == 'fr')
                                            <span class="d-block text-muted fw-light">
                                                {{ __('classes.children_selected') }}
                                            </span>
                                        @endif
                                    </div>

                                    <div id="selected-children" class="role-list mt-2">
                                        <div class="role-list-empty-message children-empty-message">
                                            {{ __('classes.children_empty', [], 'eus') }}
                                            @if (Lang::getLocale() == 'fr')
                                                <span class="d-block">
                                                    {{ __('classes.children_empty') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div id="children-error" class="invalid-feedback d-none mt-1">
                                        {{ __('classes.children_error', [], 'eus') }}
                                    </div>
                                </div>
                            </div>

                            {{-- Inputs hidden générés par JS --}}
                            <div id="children-inputs"></div>
                        </div>

                        @error('children')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                        @error('children.*')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Boutons d'action --}}
                    <div class="d-flex justify-content-end pt-3 mt-4">
                        <a href="{{ route('admin.classes.index') }}" class="btn btn-link text-muted me-2 px-2">
                            {{ __('classes.cancel', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr')
                                <span class="d-block small fw-light">
                                    {{ __('classes.cancel') }}
                                </span>
                            @endif
                        </a>

                        <button type="submit" class="btn btn-warning fw-semibold">
                            {{ __('classes.save', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr')
                                <span class="d-block small fw-light">
                                    {{ __('classes.save') }}
                                </span>
                            @endif
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- JS sélecteur d’enfants --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof window.initChildrenSelector !== 'function') {
                    console.warn('initChildrenSelector non disponible');
                    return;
                }

                window.initChildrenSelector({
                    initialSelectedIds: @json($selectedChildrenIds ?? []),
                    debugLabel: 'classes.edit',
                });
            });
        </script>
    @endpush
</x-app-layout>
