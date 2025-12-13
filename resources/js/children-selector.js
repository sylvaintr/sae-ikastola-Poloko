// resources/js/children-selector.js

export function initChildrenSelector(options = {}) {
    const {
        childSearchId = 'child-search',
        availableBoxId = 'available-children',
        selectedBoxId = 'selected-children',
        childrenInputsId = 'children-inputs',
        childrenErrorId = 'children-error',
        formSelector = 'form',
        initialSelectedIds = [],
        requireAtLeastOne = true,
        debugLabel = 'classes',
    } = options;

    const childSearch = document.getElementById(childSearchId);
    const availableBox = document.getElementById(availableBoxId);
    const selectedBox = document.getElementById(selectedBoxId);
    const childrenInputs = document.getElementById(childrenInputsId);
    const childrenError = document.getElementById(childrenErrorId);
    const form = document.querySelector(formSelector);

    if (!childSearch || !availableBox || !selectedBox || !childrenInputs) {
        console.warn(`❌ initChildrenSelector(${debugLabel}): éléments introuvables`, {
            childSearch,
            availableBox,
            selectedBox,
            childrenInputs,
        });
        return;
    }

    const items = Array.from(availableBox.querySelectorAll('.child-item'));
    console.log(`👶 [${debugLabel}] élèves trouvés :`, items.length);

    const selectedIds = new Set();

    function updateEmptyMessage() {
        const msg = selectedBox.querySelector('.children-empty-message');
        if (!msg) return;

        if (selectedIds.size === 0) {
            msg.classList.remove('d-none');
        } else {
            msg.classList.add('d-none');
        }
    }

    function validateChildren() {
        if (!childrenError || !requireAtLeastOne) return true;

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
        const id = item.dataset.childId;
        const name = item.dataset.childName;

        if (!id || selectedIds.has(id)) return;

        selectedIds.add(id);
        item.classList.add('d-none');

        const row = document.createElement('div');
        row.className = 'role-item selected child-selected';
        row.dataset.childId = id;
        row.dataset.childName = name;

        const span = document.createElement('span');
        span.textContent = name;

        const icon = document.createElement('i');
        icon.className = 'bi bi-x-circle';
        icon.style.cursor = 'pointer';

        row.appendChild(span);
        row.appendChild(icon);
        selectedBox.appendChild(row);

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'children[]';
        input.value = id;
        childrenInputs.appendChild(input);

        updateEmptyMessage();
        validateChildren();
    }

    function removeChild(id) {
        if (!selectedIds.has(id)) return;

        selectedIds.delete(id);

        const row = selectedBox.querySelector('[data-child-id="' + id + '"]');
        if (row) row.remove();

        const input = childrenInputs.querySelector('input[value="' + id + '"]');
        if (input) input.remove();

        const item = availableBox.querySelector('[data-child-id="' + id + '"]');
        if (item) item.classList.remove('d-none');

        updateEmptyMessage();
        validateChildren();
    }

    function filterList(query) {
        const q = query.toLowerCase().trim();
        console.log(`🔎 [${debugLabel}] filterList =`, q);

        for (const item of items) {
            const id = item.dataset.childId;
            const name = (item.dataset.childName || '').toLowerCase();

            if (selectedIds.has(id)) {
                item.classList.add('d-none');
                continue;
            }

            if (q === '' || name.includes(q)) {
                item.classList.remove('d-none');
            } else {
                item.classList.add('d-none');
            }
        }
    }

    // 🔗 événements
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
        filterList(e.target.value);
    });

    childSearch.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
        }
    });

    if (form) {
        form.addEventListener('submit', function (e) {
            if (!validateChildren()) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    }

    // ✅ pré-sélection pour l’édition
    initialSelectedIds.forEach(function (id) {
        const item = availableBox.querySelector('[data-child-id="' + id + '"]');
        if (item) {
            addChild(item);
        }
    });

    // init
    filterList('');
    updateEmptyMessage();
    console.log(`✅ initChildrenSelector(${debugLabel}) prêt`);
}

// On l’expose en global pour les Blade
if (typeof globalThis.window !== 'undefined') {
    window.initChildrenSelector = initChildrenSelector;
}
