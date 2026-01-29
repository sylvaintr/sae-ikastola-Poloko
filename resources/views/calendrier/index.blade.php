@php
    use Illuminate\Support\Str;
@endphp

{{-- Modal détail événement --}}
<div class="modal fade" id="eventDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="eventDetailTitle">{{ __('evenements.title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('evenements.calendar_close') }}"></button>
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
                    {{ __('evenements.calendar_close') }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal synchronisation calendrier --}}
<div class="modal fade" id="syncCalendarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-calendar-check me-2"></i>
                    <span class="basque">{{ Lang::get('evenements.sync_title', [], 'eus') }}</span>
                    @if (Lang::getLocale() == 'fr')
                        <span class="fr text-muted">/ {{ Lang::get('evenements.sync_title') }}</span>
                    @endif
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('evenements.calendar_close') }}"></button>
            </div>

            <div class="modal-body pt-2">
                <p class="text-muted mb-3">
                    {{ __('evenements.sync_description') }}
                </p>

                <div class="mb-4">
                    <label for="ics-url" class="form-label fw-semibold">
                        <span class="basque">{{ Lang::get('evenements.sync_url', [], 'eus') }}</span>
                        @if (Lang::getLocale() == 'fr')
                            <span class="fr text-muted">/ {{ Lang::get('evenements.sync_url') }}</span>
                        @endif
                    </label>
                    <div class="input-group">
                        <input type="text" id="ics-url" class="form-control"
                               value="{{ Auth::user()->getIcsUrl() }}" readonly>
                        <button type="button" class="btn demande-btn-outline" id="copy-ics-url"
                                title="{{ __('evenements.copy_url') }}">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <div id="copy-success" class="small text-success mt-1" style="display: none;">
                        <i class="bi bi-check-circle me-1"></i>{{ __('evenements.url_copied') }}
                    </div>
                </div>

                <div class="accordion" id="syncInstructions">
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#googleInstructions">
                                <i class="bi bi-google me-2"></i> Google Agenda
                            </button>
                        </h2>
                        <div id="googleInstructions" class="accordion-collapse collapse" data-bs-parent="#syncInstructions">
                            <div class="accordion-body small text-muted">
                                <ol class="ps-3 mb-0">
                                    <li>{{ __('evenements.google_step_1') }}</li>
                                    <li>{{ __('evenements.google_step_2') }}</li>
                                    <li>{{ __('evenements.google_step_3') }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#outlookInstructions">
                                <i class="bi bi-microsoft me-2"></i> Outlook
                            </button>
                        </h2>
                        <div id="outlookInstructions" class="accordion-collapse collapse" data-bs-parent="#syncInstructions">
                            <div class="accordion-body small text-muted">
                                <ol class="ps-3 mb-0">
                                    <li>{{ __('evenements.outlook_step_1') }}</li>
                                    <li>{{ __('evenements.outlook_step_2') }}</li>
                                    <li>{{ __('evenements.outlook_step_3') }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#appleInstructions">
                                <i class="bi bi-apple me-2"></i> Apple Calendar
                            </button>
                        </h2>
                        <div id="appleInstructions" class="accordion-collapse collapse" data-bs-parent="#syncInstructions">
                            <div class="accordion-body small text-muted">
                                <ol class="ps-3 mb-0">
                                    <li>{{ __('evenements.apple_step_1') }}</li>
                                    <li>{{ __('evenements.apple_step_2') }}</li>
                                    <li>{{ __('evenements.apple_step_3') }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <span class="small text-muted">{{ __('evenements.regenerate_hint') }}</span>
                    </div>
                    <form method="post" action="{{ route('profile.regenerate-ics-token') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                onclick="return confirm('{{ __('evenements.confirm_regenerate') }}')">
                            <i class="bi bi-arrow-repeat me-1"></i>
                            {{ __('evenements.regenerate_token') }}
                        </button>
                    </form>
                </div>

                @if (session('status') === 'ics-token-regenerated')
                    <div class="alert alert-success mt-3 mb-0 py-2">
                        <i class="bi bi-check-circle me-1"></i>
                        {{ __('evenements.token_regenerated') }}
                    </div>
                @endif
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn demande-btn-outline" data-bs-dismiss="modal">
                    {{ __('evenements.calendar_close') }}
                </button>
            </div>
        </div>
    </div>
</div>


<x-app-layout>
    <div class="container py-4 calendrier-page demande-page">

        {{-- Titre bilingue --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
            <div>
                <h1 class="text-capitalize mb-0">
                    {{ Lang::get('evenements.calendar_title', [], 'eus') }}
                </h1>
                @if (Lang::getLocale() == 'fr')
                    <p class="text-capitalize mb-0 text-muted">
                        {{ Lang::get('evenements.calendar_title') }}
                    </p>
                @endif
            </div>

            {{-- Toolbar --}}
            <div class="d-flex flex-nowrap gap-3 align-items-start">
                <div class="d-flex flex-column align-items-center">
                    <button type="button" class="btn demande-btn-outline fw-semibold px-4 py-2"
                            data-bs-toggle="modal" data-bs-target="#syncCalendarModal">
                        <i class="bi bi-calendar-check me-1"></i>
                        {{ Lang::get('evenements.sync_button', [], 'eus') }}
                    </button>
                    @if (Lang::getLocale() == 'fr')
                        <small class="text-muted mt-1">{{ Lang::get('evenements.sync_button') }}</small>
                    @endif
                </div>

                <div class="d-flex flex-column align-items-center">
                    <a href="{{ route('evenements.create') }}"
                        class="btn demande-btn-primary fw-semibold text-white px-4 py-2">
                        {{ Lang::get('evenements.add', [], 'eus') }}
                    </a>
                    @if (Lang::getLocale() == 'fr')
                        <small class="text-muted mt-1">{{ Lang::get('evenements.add') }}</small>
                    @endif
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
                    <button type="button" class="btn-close btn-close-sm" aria-label="{{ __('evenements.calendar_close') }}"></button>
                </div>
            </div>
        @endif

        <div class="mt-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 p-md-4">
                    <div id="calendar-root"
                         data-events-url="{{ route('calendrier.events') }}"
                         data-locale="{{ Lang::getLocale() === 'eus' ? 'eu' : 'fr' }}"
                         data-event-obligatoire="{{ __('evenements.event_obligatoire') }}"
                         data-no-description="{{ __('evenements.no_description') }}"
                         data-demande-label="{{ __('demandes.title') }}"
                         data-demande-urgence="{{ __('demandes.urgence') }}"
                         data-demande-etat="{{ __('demandes.etat') }}"
                         data-demande-show-url="{{ route('demandes.show', ['demande' => '__ID__']) }}"
                         style="min-height: 650px;"></div>

                    {{-- Légende --}}
                    <div class="mt-4 pt-3 border-top">
                        <p class="fw-semibold mb-2 small">
                            <span class="basque">{{ Lang::get('evenements.legend', [], 'eus') }}</span>
                            @if (Lang::getLocale() == 'fr')
                                <span class="fr text-muted">/ {{ Lang::get('evenements.legend') }}</span>
                            @endif
                        </p>
                        <div class="d-flex flex-wrap gap-3 small">
                            {{-- Événements --}}
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded" style="width: 16px; height: 16px; background-color: #3788d8; display: inline-block;"></span>
                                <span class="basque">{{ Lang::get('evenements.legend_event', [], 'eus') }}</span>
                                @if (Lang::getLocale() == 'fr')
                                    <span class="fr text-muted">/ {{ Lang::get('evenements.legend_event') }}</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded" style="width: 16px; height: 16px; background-color: #dc3545; display: inline-block;"></span>
                                <span class="basque">{{ Lang::get('evenements.legend_event_obligatoire', [], 'eus') }}</span>
                                @if (Lang::getLocale() == 'fr')
                                    <span class="fr text-muted">/ {{ Lang::get('evenements.legend_event_obligatoire') }}</span>
                                @endif
                            </div>
                            {{-- Demandes --}}
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded" style="width: 16px; height: 16px; background-color: #dc3545; display: inline-block;"></span>
                                <span class="basque">{{ Lang::get('evenements.legend_demande_high', [], 'eus') }}</span>
                                @if (Lang::getLocale() == 'fr')
                                    <span class="fr text-muted">/ {{ Lang::get('evenements.legend_demande_high') }}</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded" style="width: 16px; height: 16px; background-color: #fd7e14; display: inline-block;"></span>
                                <span class="basque">{{ Lang::get('evenements.legend_demande_medium', [], 'eus') }}</span>
                                @if (Lang::getLocale() == 'fr')
                                    <span class="fr text-muted">/ {{ Lang::get('evenements.legend_demande_medium') }}</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded" style="width: 16px; height: 16px; background-color: #ffc107; display: inline-block;"></span>
                                <span class="basque">{{ Lang::get('evenements.legend_demande_low', [], 'eus') }}</span>
                                @if (Lang::getLocale() == 'fr')
                                    <span class="fr text-muted">/ {{ Lang::get('evenements.legend_demande_low') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @vite('resources/js/calendar.js')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toast de statut
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

            // Copier l'URL ICS
            const copyBtn = document.getElementById('copy-ics-url');
            if (copyBtn) {
                copyBtn.addEventListener('click', function() {
                    const urlInput = document.getElementById('ics-url');
                    urlInput.select();
                    urlInput.setSelectionRange(0, 99999);
                    navigator.clipboard.writeText(urlInput.value).then(function() {
                        const successMsg = document.getElementById('copy-success');
                        successMsg.style.display = 'block';
                        setTimeout(function() {
                            successMsg.style.display = 'none';
                        }, 2000);
                    });
                });
            }
        });
    </script>
</x-app-layout>
