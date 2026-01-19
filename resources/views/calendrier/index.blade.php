@php
    use Illuminate\Support\Str;
@endphp

{{-- Modal détail événement --}}
<div class="modal fade" id="eventDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="eventDetailTitle">Événement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>

            <div class="modal-body pt-2">
                <div class="mb-2 text-muted" id="eventDetailDate"></div>

                <div class="mb-3">
                    <span id="eventDetailObligatoire" class="badge rounded-pill d-none"></span>
                </div>

                <div id="eventDetailDescription" class="fs-6 text-secondary">
                    —
                </div>
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn demande-btn-outline" data-bs-dismiss="modal">
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>


<x-app-layout>
    <div class="container py-4 calendrier-page">

        {{-- Toolbar (tu peux brancher ces boutons plus tard) --}}
        <div class="calendrier-toolbar text-end">
            <div class="d-flex flex-wrap gap-4 justify-content-end">
                <div class="calendrier-toolbar-item">
                    <button type="button" class="btn demande-btn-outline fw-semibold px-4 py-2">
                        Export
                    </button>
                    <small class="text-muted d-block">Exporter le calendrier</small>
                </div>

                <div class="calendrier-toolbar-item">
                    <a href="{{ route('evenements.create') }}"
                        class="btn demande-btn-primary fw-semibold text-white px-4 py-2">
                        Ajouter
                    </a>
                    <small class="text-muted d-block">Créer un événement</small>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div id="calendrier-toast" class="demande-toast shadow-sm">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-sm" aria-label="Fermer"></button>
                </div>
            </div>
        @endif

        <div class="mt-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 p-md-4">
                    <div id="calendar-root" data-events-url="{{ route('calendrier.events') }}"
                        style="min-height: 650px;"></div>
                </div>
            </div>
        </div>
    </div>

    @vite('resources/js/calendar.js')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.getElementById('calendrier-toast');
            if (toast) {
                const closeBtn = toast.querySelector('.btn-close');
                const hideToast = () => {
                    toast.classList.add('hide');
                    setTimeout(() => toast.remove(), 250);
                };
                closeBtn?.addEventListener('click', hideToast);
                setTimeout(hideToast, 3200);
            }
        });
    </script>
</x-app-layout>
