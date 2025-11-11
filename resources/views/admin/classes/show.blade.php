<x-app-layout>
    <div class="container py-4">
        <a href="{{ route('admin.classes.index') }}" class="admin-back-link mb-4 d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <span>{{ __('admin.classes_page.back') }}</span>
        </a>

        <div class="card border-0 shadow-sm mb-5">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="d-flex flex-column flex-md-row align-items-md-center gap-4">
                        <h1 class="h4 fw-bold mb-0">{{ $classe->nom }}</h1>
                        <div class="d-flex align-items-center gap-2 text-muted">
                            <span class="fw-semibold text-dark">{{ __('admin.classes_page.show.level_label') }} :</span>
                            <span>{{ $classe->niveau }}</span>
                        </div>
                    </div>
                    <a href="{{ route('admin.classes.edit', $classe) }}" class="btn btn-sm fw-semibold d-inline-flex align-items-center gap-2 admin-submit-btn">
                        <i class="bi bi-pencil-square"></i>
                        <span>{{ __('admin.classes_page.actions.edit') }}</span>
                    </a>
                </div>
            </div>
        </div>

        <section>
            <h2 class="fw-bold fs-3 mb-4">{{ __('admin.classes_page.students.title') }}</h2>

            <div class="table-responsive">
                <table class="table align-middle admin-table">
                    <thead>
                        <tr>
                            @foreach (__('admin.classes_page.students.columns') as $column)
                                <th scope="col">
                                    <span class="admin-table-heading">{{ $column['title'] }}</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($classe->enfants as $enfant)
                            <tr>
                                <td>{{ $enfant->idEnfant }}</td>
                                <td>{{ $enfant->nom }}</td>
                                <td>{{ $enfant->prenom }}</td>
                                <td>{{ optional($enfant->dateN)->format('d/m/Y') ?? '—' }}</td>
                                <td>
                                    @if ($enfant->sexe === 'M')
                                        {{ __('gender.male') }}
                                    @elseif ($enfant->sexe === 'F')
                                        {{ __('gender.female') }}
                                    @else
                                        {{ $enfant->sexe ?? '—' }}
                                    @endif
                                </td>
                                <td>{{ $enfant->NNI }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    {{ __('admin.classes_page.students.empty') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>

