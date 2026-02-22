<section>
    <header>
        <h2 class="h5 fw-semibold">
            {{ __('auth.abonnement_calendrier') }}
        </h2>

        <p class="small text-muted">
            {{ __('auth.abonnement_calendrier_description') }}
        </p>
    </header>

    <div class="mt-3">
        <div class="mb-3">
            <label for="ics-url" class="form-label">{{ __('auth.url_calendrier') }}</label>
            <div class="input-group">
                <input type="text" id="ics-url" class="form-control"
                       value="{{ Auth::user()->getIcsUrl() }}" readonly>
                <button type="button" class="btn btn-outline-secondary" id="copy-ics-url"
                        title="{{ __('auth.copier_url') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                        <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                    </svg>
                </button>
            </div>
            <div id="copy-success" class="small text-success mt-1" style="display: none;">
                {{ __('auth.url_copiee') }}
            </div>
        </div>

        <div class="mb-4">
            <details class="mb-3">
                <summary class="text-muted small" style="cursor: pointer;">
                    {{ __('auth.instructions_calendrier') }}
                </summary>
                <div class="mt-2 ps-3 small text-muted">
                    <p><strong>Google Agenda :</strong></p>
                    <ol class="ps-3">
                        <li>{{ __('auth.google_etape_1') }}</li>
                        <li>{{ __('auth.google_etape_2') }}</li>
                        <li>{{ __('auth.google_etape_3') }}</li>
                    </ol>

                    <p class="mt-2"><strong>Outlook :</strong></p>
                    <ol class="ps-3">
                        <li>{{ __('auth.outlook_etape_1') }}</li>
                        <li>{{ __('auth.outlook_etape_2') }}</li>
                        <li>{{ __('auth.outlook_etape_3') }}</li>
                    </ol>

                    <p class="mt-2"><strong>Apple Calendar :</strong></p>
                    <ol class="ps-3">
                        <li>{{ __('auth.apple_etape_1') }}</li>
                        <li>{{ __('auth.apple_etape_2') }}</li>
                        <li>{{ __('auth.apple_etape_3') }}</li>
                    </ol>
                </div>
            </details>
        </div>

        <form method="post" action="{{ route('profile.regenerate-ics-token') }}" class="mt-3">
            @csrf
            <p class="small text-muted mb-2">
                {{ __('auth.regenerer_token_description') }}
            </p>
            <button type="submit" class="btn btn-outline-danger btn-sm"
                    onclick="return confirm('{{ __('auth.confirmer_regenerer_token') }}')">
                {{ __('auth.regenerer_token') }}
            </button>
            @if (session('status') === 'ics-token-regenerated')
                <span class="small text-success ms-2" id="token-regenerated-msg">
                    {{ __('auth.token_regenere') }}
                </span>
                <script>
                    (function () {
                        var el = document.getElementById('token-regenerated-msg');
                        if (!el) return;
                        setTimeout(function () { el.style.display = 'none'; }, 3000);
                    })();
                </script>
            @endif
        </form>
    </div>

    <script>
        document.getElementById('copy-ics-url').addEventListener('click', function() {
            var urlInput = document.getElementById('ics-url');
            urlInput.select();
            urlInput.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(urlInput.value).then(function() {
                var successMsg = document.getElementById('copy-success');
                successMsg.style.display = 'block';
                setTimeout(function() {
                    successMsg.style.display = 'none';
                }, 2000);
            });
        });
    </script>
</section>
