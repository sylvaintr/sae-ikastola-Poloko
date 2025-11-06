<x-app-layout>
    <div class="container py-4">
		<div class="mb-3">
			<div class="d-flex justify-content-between align-items-end">
				<ul class="nav nav-tabs border-0">
                <li class="nav-item me-3">
                    <a class="nav-link active fw-bold text-warning" href="#" aria-current="page" style="border: none;">{{ __('presence.cantine') }}</a>
                </li>
                <li class="nav-item me-3">
                    <a class="nav-link text-secondary" href="#" style="border: none;">{{ __('presence.garderie_matin') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-secondary" href="#" style="border: none;">{{ __('presence.garderie_soir') }}</a>
                </li>
				</ul>

				<div class="d-flex align-items-center gap-2">
					<div id="display-date" class="fw-semibold me-1" style="font-size: 1.05rem;"></div>
					<button id="open-date" type="button" class="btn btn-link p-0" aria-label="Choisir la date">
						<i class="bi bi-chevron-down" style="font-size: 1.1rem;"></i>
					</button>
					<input id="presence-date" name="date" type="date" value="{{ now()->toDateString() }}" style="position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none;" />
				</div>
			</div>
            <div style="height: 3px; background-color: #e48a1f; margin-top: 4px;"></div>
        </div>

        salut
    </div>
</x-app-layout>

<script>
    (function() {
        const input = document.getElementById('presence-date');
        const out = document.getElementById('display-date');
        const btn = document.getElementById('open-date');
        function formatFr(dateStr) {
            try {
                const d = new Date(dateStr);
                const txt = d.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });
                return txt.charAt(0).toUpperCase() + txt.slice(1);
            } catch (_) { return ''; }
        }
        function render() { out.textContent = formatFr(input.value); }
        input.addEventListener('change', render);
        btn.addEventListener('click', function() {
            if (typeof input.showPicker === 'function') {
                input.showPicker();
            } else {
                input.click();
            }
        });
        render();
    })();
</script>


