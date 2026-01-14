<a href="{{ route('admin.facture.show', $facture->idFacture) }}" class="text-decoration-none text-black"><i
        class="bi bi-eye me-3"></i></a>

@if ($facture->etat !== 'verifier' && $facture->etat !== 'manuel verifier')
    <a href="#" class="text-decoration-none text-black" data-bs-toggle="modal"
        data-bs-target="#modal-modifier-facture-{{ $facture->idFacture }}">

        <i class="bi bi-pencil-fill me-3"></i>
    </a>
    <x-modal :name="'modifier-facture-' . $facture->idFacture" :maxWidth="'lg'">
        <form id="form-modifier-facture-{{ $facture->idFacture }}" action="{{ route('admin.facture.update', $facture->idFacture) }}" method="POST" enctype="multipart/form-data">
        <div class="modal-body p-4 pb-3 ">
            <h2 class="h5 fw-semibold mb-3">
                {{ __('facture.remplacer', [], 'eus') }}
                @if (Lang::getLocale() == 'fr')
                    <p class="fw-light mb-0 text-break">{{ __('facture.remplacer') }}</p>
                @endif
            </h2>
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="facture" class="form-label">{{ __('facture.selectionner_facture', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr')
                            <p class="fw-light mb-0 text-break">{{ __('facture.selectionner_facture') }}</p>
                        @endif
                    </label>
                   <input type="file"
                           class="form-control"
                           id="input-facture-{{ $facture->idFacture }}"
                           name="facture"
                           accept=".doc,.docx,.odt,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.oasis.opendocument.text"
                           required>
                   <div id="error-facture-{{ $facture->idFacture }}" class="alert alert-danger d-none mt-2" role="alert"></div>
                </div>


        </div>
        <div class="modal-footer border-0 pt-0">
            <div class="d-flex justify-content-end">
                <div>
                <x-secondary-button type="button" data-bs-dismiss="modal" class="me-3">
                    {{ __('auth.annuler') }}
                    
                </x-secondary-button>
               @if (Lang::getLocale() == 'fr')
                    <p class="fw-light mb-0">{{ __('auth.annuler') }}</p>
                @endif
                </div>
                <div>
                    <button type="submit" class="btn">
                        {{ __('facture.valider_facture', [], 'eus') }}
                    </button>
                    @if (Lang::getLocale() == 'fr')
                        <p class="fw-light mb-0">{{ __('facture.valider_facture') }}</p>
                    @endif
                </div>
                

            </div>
        </div>
        </form>
    </x-modal>

    <a href="#" class="text-decoration-none text-black" data-bs-toggle="modal"
        data-bs-target="#modal-{{ $facture->idFacture }}">
        <i class="bi bi-check-lg me-3"></i>
    </a>
    <x-modal :name="$facture->idFacture" :maxWidth="'lg'">
        <div class="modal-body p-4 pb-3 ">
            <h2 class="h5 fw-semibold mb-3">
                {{ __('facture.confirmer_validation', [], 'eus') }}
                @if (Lang::getLocale() == 'fr')
                    <p class="fw-light mb-0 text-break">{{ __('facture.confirmer_validation') }}</p>
                @endif
            </h2>

            <div class="small text-muted mb-2  text-break">
                {{ __('facture.texte_confirmation_validation', [], 'eus') }}
                @if (Lang::getLocale() == 'fr')
                    <p class="fw-light mb-0 text-break">{{ __('facture.texte_confirmation_validation') }}</p>
                @endif
            </div>
        </div>
        <div class="modal-footer border-0 pt-0">
            <div class="d-flex justify-content-end">
                <x-secondary-button type="button" data-bs-dismiss="modal">
                    {{ __('auth.annuler') }}
                    @if (Lang::getLocale() == 'fr')
                        <p class="fw-light mb-0">{{ __('auth.annuler') }}</p>
                    @endif
                </x-secondary-button>
                <form action="{{ route('admin.facture.valider', $facture->idFacture) }}" class="ms-3">
                    @csrf
                    <x-danger-button>
                        {{ __('facture.valider_facture', [], 'eus') }}
                        @if (Lang::getLocale() == 'fr')
                            <p class="fw-light mb-0">{{ __('facture.valider_facture') }}</p>
                        @endif
                    </x-danger-button>
                </form>

            </div>
        </div>
    </x-modal>
@else
    <a href="{{ route('admin.facture.envoyer', $facture->idFacture) }}" class="text-decoration-none text-black"><i
            class="bi bi-send-fill me-3"></i></a>
@endif

<a href="{{ route('admin.facture.export', $facture->idFacture) }}" class="text-decoration-none text-black"><i
        class="bi bi-download me-3"></i></a>

