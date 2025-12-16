<div class="d-inline-flex gap-2">
    <a href="{{ route('admin.etiquettes.edit', $etiquette->idEtiquette) }}" class="btn demande-action-btn" title="{{ __('etiquette.action_edit') }}">
        <i class="bi bi-pencil-fill"></i>
    </a>

    @php $formId = 'del-etiquette-' . $etiquette->idEtiquette; @endphp
    <form id="{{ $formId }}" action="{{ route('admin.etiquettes.destroy', $etiquette->idEtiquette) }}" method="POST" class="d-inline"
        onsubmit="return confirm('{{ __('etiquette.confirm_delete_message') ?? 'Supprimer ?' }}');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn demande-action-btn text-muted" title="{{ __('etiquette.action_delete') }}">
            <i class="bi bi-trash-fill"></i>
        </button>
    </form>
</div>
