{{-- Modal du guide d'utilisation interactif --}}
@php
    // Récupérer toutes les étapes depuis les traductions
    $allSteps = __('guide.steps');

    // Filtrer les étapes selon les permissions de l'utilisateur
    $steps = [];
    foreach ($allSteps as $key => $step) {
        $permission = $step['permission'] ?? null;

        // Si pas de permission requise, ou si l'utilisateur a la permission
        if ($permission === null || Auth::user()->can($permission)) {
            $steps[] = [
                'key' => $key,
                'title' => $step['title'],
                'icon' => $step['icon'],
                'description' => $step['description'],
                'video' => $step['video'] ?? null,
            ];
        }
    }

    $totalSteps = count($steps);
@endphp

<div
    class="modal fade"
    id="userGuideModal"
    tabindex="-1"
    aria-labelledby="userGuideModalLabel"
    aria-hidden="true"
    x-data="userGuide()"
    x-init="init()"
>
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content user-guide-modal">
            {{-- Header --}}
            <div class="modal-header user-guide-header">
                <h5 class="modal-title" id="userGuideModalLabel">
                    <i class="bi bi-book me-2"></i>
                    {{ __('guide.modal_title') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('guide.close') }}"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body user-guide-body p-0">
                <div class="row g-0 h-100">
                    {{-- Sidebar avec les étapes --}}
                    <div class="col-md-4 user-guide-sidebar">
                        <nav class="user-guide-nav">
                            @foreach($steps as $index => $step)
                                <button
                                    type="button"
                                    class="user-guide-nav-item"
                                    :class="{ 'active': currentStep === {{ $index }}, 'completed': {{ $index }} < currentStep }"
                                    @click="goToStep({{ $index }})"
                                >
                                    <span class="user-guide-nav-indicator">
                                        <template x-if="{{ $index }} < currentStep">
                                            <i class="bi bi-check-lg"></i>
                                        </template>
                                        <template x-if="{{ $index }} >= currentStep">
                                            <span>{{ $index + 1 }}</span>
                                        </template>
                                    </span>
                                    <span class="user-guide-nav-title">{{ $step['title'] }}</span>
                                </button>
                            @endforeach
                        </nav>
                    </div>

                    {{-- Contenu de l'étape --}}
                    <div class="col-md-8 user-guide-content">
                        @foreach($steps as $index => $step)
                            <div
                                class="user-guide-step"
                                x-show="currentStep === {{ $index }}"
                                x-transition:enter="user-guide-step-enter"
                                x-transition:enter-start="user-guide-step-enter-start"
                                x-transition:enter-end="user-guide-step-enter-end"
                            >
                                <div class="user-guide-step-icon">
                                    <i class="bi {{ $step['icon'] }}"></i>
                                </div>
                                <h4 class="user-guide-step-title">{{ $step['title'] }}</h4>
                                <p class="user-guide-step-description">{{ $step['description'] }}</p>

                                @if($step['video'])
                                    <div class="user-guide-video mt-3">
                                        <div class="ratio ratio-16x9">
                                            <iframe
                                                src="{{ $step['video'] }}"
                                                title="{{ $step['title'] }}"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                allowfullscreen
                                            ></iframe>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Footer avec navigation --}}
            <div class="modal-footer user-guide-footer">
                <div class="user-guide-progress">
                    <span x-text="currentStep + 1"></span>
                    <span>{{ __('guide.step_of') }}</span>
                    <span>{{ $totalSteps }}</span>
                </div>

                <div class="user-guide-actions">
                    <button
                        type="button"
                        class="btn btn-outline-secondary user-guide-btn-prev"
                        @click="prevStep()"
                        :disabled="currentStep === 0"
                        x-show="currentStep > 0"
                    >
                        <i class="bi bi-chevron-left me-1"></i>
                        {{ __('guide.previous') }}
                    </button>

                    <button
                        type="button"
                        class="btn btn-primary user-guide-btn-next"
                        @click="nextStep()"
                        x-show="currentStep < {{ $totalSteps - 1 }}"
                    >
                        {{ __('guide.next') }}
                        <i class="bi bi-chevron-right ms-1"></i>
                    </button>

                    <button
                        type="button"
                        class="btn btn-success user-guide-btn-finish"
                        data-bs-dismiss="modal"
                        @click="finish()"
                        x-show="currentStep === {{ $totalSteps - 1 }}"
                    >
                        <i class="bi bi-check-lg me-1"></i>
                        {{ __('guide.finish') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
