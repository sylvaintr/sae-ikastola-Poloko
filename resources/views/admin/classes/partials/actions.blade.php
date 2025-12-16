<div class="d-inline-flex gap-2">
    <a href="{{ route('admin.classes.show', $classe) }}" class="btn demande-action-btn" title="{{ __('classes.action_view') }}">
        <i class="bi bi-eye-fill"></i>
    </a>

    <a href="{{ route('admin.classes.edit', $classe) }}" class="btn demande-action-btn" title="{{ __('classes.action_edit') }}">
        <i class="bi bi-pencil-fill"></i>
    </a>

    <form action="{{ route('admin.classes.destroy', $classe) }}" method="POST" class="d-inline"
        onsubmit="return confirm('{{ __('classes.confirm_delete') }}');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn demande-action-btn text-muted" title="{{ __('classes.action_delete') }}">
            <i class="bi bi-trash-fill"></i>
        </button>
    </form>
</div>
