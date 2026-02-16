<x-app-layout>
    <div class="container-fluid py-4 profile-page">
        <div class="row">
            <!-- Section 1: Mon profil -->
            <div class="col-12 col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h4 fw-bold mb-4">{{ __('auth.mon_profil') }}</h2>

                        <div class="d-flex flex-column flex-sm-row align-items-start">
                            <!-- Photo de profil -->
                            <div class="me-0 me-sm-4 mb-3 mb-sm-0" style="flex-shrink: 0;">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                     style="width: 100px; height: 100px; overflow: hidden; background-color: #f5e6d3;">
                                    @php
                                        $initial = Auth::user()->nom ?: Auth::user()->prenom;
                                    @endphp
                                    @if($initial)
                                        <span class="text-dark" style="font-size: 2rem;">{{ strtoupper(substr($initial, 0, 1)) }}</span>
                                    @else
                                        <span class="text-dark" style="font-size: 2rem;">U</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Informations -->
                            <div class="flex-grow-1 w-100">
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
                                            {{ __('auth.default_role') ?? 'N/A' }}
                                        @endif
                                    </span>
                                </div>

                                <div class="mb-3">
                                    <span class="text-muted small">{{ __('auth.statut_compte') }} :</span>
                                    <span class="fw-semibold">
                                        @if(Auth::user()->email_verified_at)
                                            {{ __('auth.valide') }}
                                        @else
                                            {{ __('auth.en_attente') ?? 'En attente' }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Informations de la famille -->
            <div class="col-12 col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h4 fw-bold mb-4">{{ __('auth.informations_famille') ?? 'Informations de la famille' }}</h2>

                        @if($user->familles->count() > 0)
                            @foreach($user->familles as $famille)
                                <div class="mb-4">
                                    <div class="mb-3">
                                        <span class="text-muted small">{{ __('auth.famille_id') ?? 'Famille ID' }} :</span>
                                        <span class="fw-semibold">#{{ $famille->idFamille }}</span>
                                    </div>

                                    @if($famille->pivot->parite)
                                        <div class="mb-3">
                                            <span class="text-muted small">{{ __('auth.parite') ?? 'Parité' }} :</span>
                                            <span class="fw-semibold">{{ $famille->pivot->parite }}</span>
                                        </div>
                                    @endif

                                    @if($famille->enfants->count() > 0)
                                        <div class="mb-3">
                                            <span class="text-muted small d-block mb-2">{{ __('auth.enfants') ?? 'Enfants' }} :</span>
                                            <ul class="list-unstyled ms-3">
                                                @foreach($famille->enfants as $enfant)
                                                    <li class="mb-2">
                                                        <span class="fw-semibold">{{ $enfant->prenom }} {{ $enfant->nom }}</span>
                                                        @if($enfant->classe)
                                                            <span class="text-muted small"> - {{ $enfant->classe->nom }}</span>
                                                        @endif
                                                        @if($enfant->dateN)
                                                            <span class="text-muted small d-block">
                                                                {{ __('auth.date_naissance') }}: {{ \Carbon\Carbon::parse($enfant->dateN)->format('d/m/Y') }}
                                                            </span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <p class="text-muted small">{{ __('auth.aucun_enfant') ?? 'Aucun enfant enregistré' }}</p>
                                    @endif
                                </div>

                                @if(!$loop->last)
                                    <hr class="my-4">
                                @endif
                            @endforeach
                        @else
                            <p class="text-muted">{{ __('auth.aucune_famille') ?? 'Aucune famille associée à ce profil' }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Section 3: Documents obligatoires -->
            @if(isset($documentsObligatoires) && $documentsObligatoires->count() > 0)
            <div class="col-12 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h4 fw-bold mb-4">{{ __('auth.documents_obligatoires') }}</h2>

                        @if(session('status') === 'document-uploaded')
                            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                                {{ __('auth.document_uploaded') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('status') === 'document-deleted')
                            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                                {{ __('auth.document_deleted') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table align-middle admin-table">
                                <thead>
                                    <tr>
                                        <th scope="col">
                                            <div class="text-center">
                                                <span class="admin-table-heading">{{ __('auth.nom_document') }}</span>
                                            </div>
                                        </th>
                                        <th scope="col">
                                            <div class="text-center">
                                                <span class="admin-table-heading">{{ __('auth.etat_document') }}</span>
                                            </div>
                                        </th>
                                        <th scope="col">
                                            <div class="text-center">
                                                <span class="admin-table-heading">{{ __('auth.actions') }}</span>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documentsObligatoires as $docOblig)
                                        @php
                                            $etats = [
                                                'non_remis' => ['label' => __('auth.non_remis'), 'badge' => 'bg-secondary'],
                                                'remis' => ['label' => __('auth.remis'), 'badge' => 'bg-info'],
                                                'en_cours_validation' => ['label' => __('auth.en_cours_validation'), 'badge' => 'bg-warning'],
                                                'valide' => ['label' => __('auth.valide_document'), 'badge' => 'bg-success']
                                            ];
                                            $etat = $etats[$docOblig->etat] ?? $etats['non_remis'];
                                            $peutUploader = !in_array($docOblig->etat, ['en_cours_validation', 'valide']);
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold text-center">{{ $docOblig->nom }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $etat['badge'] }}">{{ $etat['label'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-2 justify-content-center flex-wrap">
                                                    @if($docOblig->documentUploaded)
                                                        <a href="{{ route('profile.document.download', $docOblig->documentUploaded->idDocument) }}"
                                                           class="btn admin-btn-download">
                                                            <i class="bi bi-download"></i> {{ __('auth.telecharger') }}
                                                        </a>

                                                        @if($docOblig->documentUploaded->etat !== 'valide')
                                                            <form action="{{ route('profile.document.delete', $docOblig->documentUploaded->idDocument) }}"
                                                                  method="POST"
                                                                  class="d-inline"
                                                                  onsubmit="return confirm('{{ __('auth.confirm_delete_document') }}');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn admin-btn-delete">
                                                                    <i class="bi bi-trash"></i> {{ __('auth.supprimer') }}
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @elseif($peutUploader)
                                                        <button type="button"
                                                                class="btn admin-btn-upload"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#uploadModal{{ $docOblig->idDocumentObligatoire }}">
                                                            <i class="bi bi-upload"></i> {{ __('auth.uploader_document') }}
                                                        </button>
                                                    @endif
                                                </div>

                                                <!-- Modal d'upload -->
                                                <div class="modal fade" id="uploadModal{{ $docOblig->idDocumentObligatoire }}" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form action="{{ route('profile.document.upload') }}" method="POST" enctype="multipart/form-data">
                                                                @csrf
                                                                <input type="hidden" name="idDocumentObligatoire" value="{{ $docOblig->idDocumentObligatoire }}">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">{{ __('auth.uploader_document') }} : {{ $docOblig->nom }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label for="document{{ $docOblig->idDocumentObligatoire }}" class="form-label">{{ __('auth.upload_document') }}</label>
                                                                        <input type="file"
                                                                               class="form-control @error('document') is-invalid @enderror"
                                                                               id="document{{ $docOblig->idDocumentObligatoire }}"
                                                                               name="document"
                                                                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                                                               required>
                                                                        @error('document')
                                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror
                                                                        <small class="text-muted">{{ __('auth.upload_document_hint') }}</small>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('auth.annuler') }}</button>
                                                                    <button type="submit" class="btn btn-primary">{{ __('auth.upload') }}</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @elseif(Auth::user()->hasAnyRole(['parent', 'CA', 'salarie']))
            <div class="col-md-12 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h4 fw-bold mb-4">{{ __('auth.documents_obligatoires') }}</h2>
                        <p class="text-muted">{{ __('auth.aucun_document_obligatoire') }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
