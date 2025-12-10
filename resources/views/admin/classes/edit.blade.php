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

                <p class="text-muted small mt-2 mb-0">
                    {{ __('classes.intro', [], 'eus') }}
                    @if (Lang::getLocale() == 'fr')
                        <span class="d-block">
                            {{ __('classes.intro') }}
                        </span>
                    @endif
                </p>
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
        @if($errors->any())
            <div class="alert alert-danger small">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $erreur)
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

                    {{-- Nom de la classe --}}
                    <div class="mb-3">
                        <label class="form-label mb-1">
                            {{ __('classes.nom', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr')
                                <span class="d-block text-muted fw-light">
                                    {{ __('classes.nom') }}
                                </span>
                            @endif
                        </label>
                        <input
                            type="text"
                            name="nom"
                            value="{{ old('nom', $classe->nom) }}"
                            class="form-control @error('nom') is-invalid @enderror"
                        >
                        @error('nom')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Niveau --}}
                    <div class="mb-4">
                        <label class="form-label mb-1">
                            {{ __('classes.niveau', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr')
                                <span class="d-block text-muted fw-light">
                                    {{ __('classes.niveau') }}
                                </span>
                            @endif
                        </label>

                        @if($levels->isNotEmpty())
                            <select
                                name="niveau"
                                class="form-select @error('niveau') is-invalid @enderror"
                            >
                                <option value="">
                                    {{ __('classes.niveau_select_placeholder', [], 'eus') }}
                                    @if (Lang::getLocale() == 'fr')
                                        - {{ __('classes.niveau_select_placeholder') }}
                                    @endif
                                </option>

                                @foreach($levels as $level)
                                    <option value="{{ $level }}"
                                        {{ old('niveau', $classe->niveau) === $level ? 'selected' : '' }}>
                                        {{ $level }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            {{-- 🔁 Fallback si aucun niveau n'existe encore --}}
                            <input
                                type="text"
                                name="niveau"
                                value="{{ old('niveau', $classe->niveau) }}"
                                placeholder="{{ __('classes.niveau_placeholder', [], 'eus') }}"
                                class="form-control @error('niveau') is-invalid @enderror"
                            >
                        @endif

                        @error('niveau')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Sélecteur d’enfants --}}
                    <div class="mb-3">
                        <div class="form-label fw-semibold mb-2">
                            {{ __('classes.children', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr')
                                <span class="d-block text-muted fw-light">
                                    {{ __('classes.children') }}
                                </span>
                            @endif
                        </div>

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

                                <input type="text" id="child-search" class="form-control form-control-sm"
                                       placeholder="{{ __('classes.children_search_placeholder', [], 'eus') }}">

                                <div id="available-children" class="border rounded mt-2 p-2 small"
                                     style="max-height: 220px; overflow-y: auto;">
                                    @foreach($children as $child)
                                        <div class="d-flex justify-content-between align-items-center py-1 px-2 child-item"
                                             data-child-id="{{ $child->idEnfant }}"
                                             data-child-name="{{ $child->prenom }} {{ $child->nom }}">
                                            <span>{{ $child->prenom }} {{ $child->nom }}</span>
                                            <i class="bi bi-plus-circle text-muted" role="button"></i>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Liste des enfants sélectionnés --}}
                            <div class="col-md-6">
                                <div class="form-label small mb-1">
                                    {{ __('classes.children_selected', [], 'eus') }} <span class="text-danger">*</span>
                                    @if (Lang::getLocale() == 'fr')
                                        <span class="d-block text-muted fw-light">
                                            {{ __('classes.children_selected') }}
                                        </span>
                                    @endif
                                </div>

                                <div id="selected-children" class="border rounded mt-2 p-2 small"
                                     style="min-height: 44px; max-height: 220px; overflow-y: auto;">
                                    <div class="text-muted small children-empty-message">
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

                        @error('children')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                        @error('children.*')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Boutons d'action --}}
                    <div class="d-flex justify-content-end pt-3 border-top mt-4">
                        <a href="{{ route('admin.classes.index') }}"
                           class="btn btn-link text-muted me-2 px-2">
                            {{ __('classes.cancel', [], 'eus') }}
                            @if (Lang::getLocale() == 'fr')
                                <span class="d-block small fw-light">
                                    {{ __('classes.cancel') }}
                                </span>
                            @endif
                        </a>

                        <button type="submit"
                                class="btn btn-warning fw-semibold">
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
            document.addEventListener('DOMContentLoaded', function () {
                const childSearch      = document.getElementById('child-search');
                const availableBox     = document.getElementById('available-children');
                const selectedBox      = document.getElementById('selected-children');
                const childrenInputs   = document.getElementById('children-inputs');
                const childrenError    = document.getElementById('children-error');
                const selectedIds      = new Set();
                const form             = document.querySelector('form');

                const childItems = Array.from(availableBox.querySelectorAll('.child-item'));

                // IDs des enfants déjà associés à la classe (passés depuis le contrôleur)
                const initialSelectedIds = @json($selectedChildrenIds ?? []);

                function updateEmptyMessage() {
                    const msg = selectedBox.querySelector('.children-empty-message');
                    if (selectedIds.size === 0) {
                        if (!msg) {
                            const div = document.createElement('div');
                            div.className = 'text-muted small children-empty-message';
                            div.textContent = '{{ __('classes.children_empty', [], 'eus') }}';
                            selectedBox.appendChild(div);
                        }
                    } else if (msg) {
                        msg.remove();
                    }
                }

                function normalize(str) {
                    return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
                }

                function filterChildren(term) {
                    const t = normalize(term.trim());
                    childItems.forEach(item => {
                        const id   = item.dataset.childId;
                        const name = normalize(item.dataset.childName || '');
                        if (selectedIds.has(id)) {
                            item.style.display = 'none';
                            return;
                        }
                        if (t === '' || name.includes(t)) {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                }

                function validateChildren() {
                    if (selectedIds.size === 0) {
                        childrenError.classList.remove('d-none');
                        childrenError.classList.add('d-block');
                        return false;
                    }
                    childrenError.classList.remove('d-block');
                    childrenError.classList.add('d-none');
                    return true;
                }

                function addChild(item) {
                    const id   = item.dataset.childId;
                    const name = item.dataset.childName;

                    if (selectedIds.has(id)) return;
                    selectedIds.add(id);
                    item.style.display = 'none';

                    // élément dans la colonne de droite
                    const row = document.createElement('div');
                    row.className = 'd-flex justify-content-between align-items-center py-1 px-2 border-bottom child-selected';
                    row.dataset.childId = id;
                    row.dataset.childName = name;

                    const span = document.createElement('span');
                    span.textContent = name;
                    const icon = document.createElement('i');
                    icon.className = 'bi bi-x-circle text-muted';
                    icon.style.cursor = 'pointer';

                    row.appendChild(span);
                    row.appendChild(icon);
                    selectedBox.appendChild(row);

                    // input hidden
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'children[]';
                    input.value = id;
                    childrenInputs.appendChild(input);

                    updateEmptyMessage();
                    validateChildren();
                }

                function removeChild(id) {
                    selectedIds.delete(id);

                    // supprimer dans sélection
                    const row = selectedBox.querySelector('[data-child-id="' + id + '"]');
                    if (row) row.remove();

                    // supprimer input hidden
                    const input = childrenInputs.querySelector('input[value="' + id + '"]');
                    if (input) input.remove();

                    // réafficher dans la liste de gauche
                    const item = availableBox.querySelector('[data-child-id="' + id + '"]');
                    if (item) item.style.display = 'flex';

                    updateEmptyMessage();
                    validateChildren();
                }

                availableBox.addEventListener('click', function (e) {
                    const item = e.target.closest('.child-item');
                    if (!item) return;
                    addChild(item);
                });

                selectedBox.addEventListener('click', function (e) {
                    const row = e.target.closest('.child-selected');
                    if (!row) return;
                    removeChild(row.dataset.childId);
                });

                childSearch.addEventListener('input', function (e) {
                    filterChildren(e.target.value);
                });

                form.addEventListener('submit', function (e) {
                    if (!validateChildren()) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                });

                // ✅ Pré-sélection des enfants déjà associés à la classe
                initialSelectedIds.forEach(function (id) {
                    const item = availableBox.querySelector('[data-child-id="' + id + '"]');
                    if (item) {
                        addChild(item);
                    }
                });

                // init filtre & message
                filterChildren('');
                updateEmptyMessage();
            });
        </script>
    @endpush
</x-app-layout>
