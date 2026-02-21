{{-- Bouton flottant pour ouvrir le guide d'utilisation --}}
<button
    type="button"
    class="user-guide-btn"
    data-bs-toggle="modal"
    data-bs-target="#userGuideModal"
    title="{{ __('guide.button_tooltip') }}"
    aria-label="{{ __('guide.button_label') }}"
>
    <i class="bi bi-question-lg"></i>
    <span class="user-guide-btn-label">{{ __('guide.button_label') }}</span>
</button>
