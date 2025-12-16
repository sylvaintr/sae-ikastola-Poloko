<div class="d-inline-flex gap-2">
    <a href="{{ route('actualites.show', $actualite->idActualite) }}" class="btn demande-action-btn" title="{{ __('Voir') }}">
        <i class="bi bi-eye"></i>
    </a>

    <a href="{{ route('admin.actualites.edit', $actualite->idActualite) }}" class="btn demande-action-btn" title="{{ __('Modifier') }}">
        <i class="bi bi-pencil-fill"></i>
    </a>

    <form action="{{ route('admin.actualites.destroy', $actualite->idActualite) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn demande-action-btn text-muted" onclick="return confirm('{{ __('Êtes-vous sûr ?') }}')" title="{{ __('Supprimer') }}">
            <i class="bi bi-trash3"></i>
        </button>
    </form>
</div>
