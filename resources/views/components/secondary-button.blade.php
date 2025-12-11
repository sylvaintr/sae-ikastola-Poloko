<button
    {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-secondary btn-outline-secondary admin-cancel-btn cancel-delete']) }}>
    {{ $slot }}
</button>
