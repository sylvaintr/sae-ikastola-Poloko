<x-app-layout>
    <div class="container-fluid py-4 profile-page">
        <div class="row">
            <!-- Section 1: Mon profil -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h4 fw-bold mb-4">{{ __('auth.mon_profil') }}</h2>
                        
                        <div class="d-flex align-items-start">
                            <!-- Photo de profil -->
                            <div class="me-4" style="flex-shrink: 0;">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" 
                                     style="width: 100px; height: 100px; overflow: hidden; background-color: #f5e6d3;">
                                    @php($initial = Auth::user()->nom ?: Auth::user()->prenom)
                                    @if($initial)
                                        <span class="text-dark" style="font-size: 2rem;">{{ strtoupper(substr($initial, 0, 1)) }}</span>
                                    @else
                                        <span class="text-dark" style="font-size: 2rem;">U</span>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Informations -->
                            <div class="flex-grow-1">
                                <div class="mb-3">
                                    <span class="text-muted small">{{ __('auth.nom') }} :</span>
                                    <span class="fw-semibold">{{ Auth::user()->nom ?? '-' }}</span>
                                </div>
                                
                                <div class="mb-3">
                                    <span class="text-muted small">{{ __('auth.prenom') }} :</span>
                                    <span class="fw-semibold">{{ Auth::user()->prenom ?? '-' }}</span>
                                </div>
                                
                                <div class="mb-3">
                                    <span class="text-muted small">{{ __('auth.date_naissance') }} :</span>
                                    <span class="fw-semibold">{{ Auth::user()->date_naissance ?? '-' }}</span>
                                </div>
                                
                                <div class="mb-3">
                                    <span class="text-muted small">{{ __('auth.role') }} :</span>
                                    <span class="fw-semibold">
                                        @if(Auth::user()->roles->count() > 0)
                                            {{ Auth::user()->roles->first()->name }}
                                        @else
                                            {{ __('auth.default_role') }}
                                        @endif
                                    </span>
                                </div>
                                
                                <div class="mb-3">
                                    <span class="text-muted small">{{ __('auth.statut_compte') }} :</span>
                                    <span class="fw-semibold">
                                        @if(Auth::user()->email_verified_at)
                                            {{ __('auth.valide') }}
                                        @else
                                            {{ __('auth.en_attente') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Section 2: Comptes liés à ce profil -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h4 fw-bold mb-0">{{ __('auth.comptes_lies') }}</h2>
                        <!-- Contenu à venir -->
                    </div>
                </div>
            </div>
            
            <!-- Section 3: Mes documents -->
            <div class="col-md-12 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h4 fw-bold mb-0">{{ __('auth.mes_documents') }}</h2>
                        <!-- Contenu à venir -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
