<a href="{{ route('admin.etiquettes.edit', $etiquette->idEtiquette) }}" class="text-decoration-none text-black">
    <i class="bi bi-pencil-fill me-3"></i>
</a>

@php $formId = 'del-etiquette-' . $etiquette->idEtiquette; @endphp
<form id="{{ $formId }}" action="{{ route('admin.etiquettes.destroy', $etiquette->idEtiquette) }}" method="POST" class="d-inline">
    @csrf
    @method('DELETE')
    <button type="button" class="btn btn-link p-0 text-decoration-none text-black border-0 btn-open-delete-modal" data-form-id="{{ $formId }}" title="Supprimer">
        <i class="bi bi-trash-fill"></i>
    </button>
</form>
