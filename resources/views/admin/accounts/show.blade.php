<x-app-layout>
    <div class="container py-4">
        <a href="{{ route('admin.accounts.index') }}" class="admin-back-link mb-4 d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <span>{{ __('admin.accounts_page.back') }}</span>
        </a>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="d-flex flex-column flex-md-row align-items-md-center gap-4">
                        <h1 class="h4 fw-bold mb-0">{{ $account->prenom }} {{ $account->nom }}</h1>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            @if ($account->statutValidation)
                                <span class="badge bg-success">{{ __('admin.accounts_page.status.validated') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('admin.accounts_page.status.not_validated') }}</span>
                            @endif
                            @if ($account->isArchived())
                                <span class="badge bg-dark">{{ __('admin.accounts_page.status.archived') }}</span>
                            @endif
                        </div>
                    </div>
                    @unless ($account->isArchived())
                        <a href="{{ route('admin.accounts.edit', $account) }}" class="btn btn-sm fw-semibold d-inline-flex align-items-center gap-2 admin-submit-btn">
                            <i class="bi bi-pencil-square"></i>
                            <span>{{ __('admin.accounts_page.actions.edit') }}</span>
                        </a>
                    @endunless
                </div>
            </div>
        </div>

        @if ($account->isArchived())
            <div class="alert alert-warning border-0 shadow-sm mb-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill"></i>
                    <span>{{ __('admin.accounts_page.show.archived_notice') }}</span>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5 fw-bold mb-4">{{ __('admin.accounts_page.show.details_title') }}</h2>
                @php
                    $languageLabels = [
                        'fr' => 'Français',
                        'eus' => 'Euskara',
                    ];
                @endphp
                <div class="row gy-3">
                    <div class="col-md-4">
                        <div class="text-muted small">{{ __('admin.accounts_page.show.email_label') }}</div>
                        <div class="fw-semibold">{{ $account->email ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">{{ __('admin.accounts_page.show.language_label') }}</div>
                        <div class="fw-semibold">{{ $languageLabels[$account->languePref] ?? strtoupper($account->languePref) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5 fw-bold mb-4">{{ __('admin.accounts_page.show.roles_label') }}</h2>
                @if ($account->rolesCustom->count() > 0)
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($account->rolesCustom as $role)
                            <span class="badge bg-primary">{{ $role->name }}</span>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">{{ __('admin.accounts_page.show.no_roles') }}</p>
                @endif
            </div>
        </div>

        @if(isset($documentsObligatoiresAvecEtat) && $documentsObligatoiresAvecEtat->count() > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5 fw-bold mb-4">{{ __('admin.accounts_page.show.documents_title') }}</h2>

                @if(session('status') === 'document_validated')
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        {{ __('admin.accounts_page.messages.document_validated') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('status') === 'document_deleted')
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        {{ __('admin.accounts_page.messages.document_deleted') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table align-middle admin-table">
                        <thead>
                            <tr>
                                <th scope="col">
                                    <div class="text-center">
                                        <span class="admin-table-heading">{{ __('admin.accounts_page.show.documents.name') }}</span>
                                    </div>
                                </th>
                                <th scope="col">
                                    <div class="text-center">
                                        <span class="admin-table-heading">{{ __('admin.accounts_page.show.documents.state') }}</span>
                                    </div>
                                </th>
                                <th scope="col">
                                    <div class="text-center">
                                        <span class="admin-table-heading">{{ __('admin.accounts_page.show.documents.date_remise') }}</span>
                                    </div>
                                </th>
                                <th scope="col">
                                    <div class="text-center">
                                        <span class="admin-table-heading">{{ __('admin.accounts_page.show.documents.actions') }}</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documentsObligatoiresAvecEtat as $docOblig)
                                @php
                                    $etats = [
                                        'non_remis' => ['label' => __('auth.non_remis'), 'badge' => 'bg-secondary'],
                                        'remis' => ['label' => __('auth.remis'), 'badge' => 'bg-info'],
                                        'en_cours_validation' => ['label' => __('auth.en_cours_validation'), 'badge' => 'bg-warning'],
                                        'valide' => ['label' => __('auth.valide_document'), 'badge' => 'bg-success']
                                    ];
                                    $etat = $etats[$docOblig->etat] ?? $etats['non_remis'];
                                @endphp
                                <tr>
                                    <td class="fw-semibold text-center">{{ $docOblig->nom }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $etat['badge'] }}">{{ $etat['label'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($docOblig->dateRemise)
                                            {{ $docOblig->dateRemise->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                                            @if($docOblig->documentUploaded)
                                                <a href="{{ route('admin.accounts.documents.download', [$account, $docOblig->documentUploaded->idDocument]) }}"
                                                   class="btn admin-btn-download">
                                                    <i class="bi bi-download"></i> {{ __('auth.telecharger') }}
                                                </a>

                                                @if($docOblig->documentUploaded->etat === 'valide')
                                                    <form action="{{ route('admin.accounts.documents.validate', [$account, $docOblig->documentUploaded]) }}"
                                                          method="POST"
                                                          class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="etat" value="en_attente">
                                                        <button type="submit"
                                                                class="btn admin-btn-invalidate"
                                                                onclick="return confirm('{{ __('admin.accounts_page.show.documents.confirm_invalidate') }}');">
                                                            <i class="bi bi-x-circle"></i> {{ __('admin.accounts_page.show.documents.invalidate') }}
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('admin.accounts.documents.validate', [$account, $docOblig->documentUploaded]) }}"
                                                          method="POST"
                                                          class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="etat" value="valide">
                                                        <button type="submit"
                                                                class="btn admin-btn-validate"
                                                                onclick="return confirm('{{ __('admin.accounts_page.show.documents.confirm_validate') }}');">
                                                            <i class="bi bi-check-circle"></i> {{ __('admin.accounts_page.show.documents.validate') }}
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($docOblig->documentUploaded->etat !== 'valide')
                                                    <form action="{{ route('admin.accounts.documents.delete', [$account, $docOblig->documentUploaded]) }}"
                                                          method="POST"
                                                          class="d-inline"
                                                          onsubmit="return confirm('{{ __('admin.accounts_page.show.documents.confirm_delete') }}');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn admin-btn-delete">
                                                            <i class="bi bi-trash"></i> {{ __('auth.supprimer') }}
                                                        </button>
                                                    </form>
                                                @endif
                                            @else
                                                <span class="text-muted">{{ __('admin.accounts_page.show.documents.not_uploaded') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>


