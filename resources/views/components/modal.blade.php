@props(['name', 'show' => false, 'maxWidth' => '2xl'])

@php
    // Map previous Tailwind width tokens to Bootstrap modal size classes
    $dialogSize = [
        'sm' => 'modal-sm',
        'md' => '',
        'lg' => 'modal-lg',
        'xl' => 'modal-xl',
        '2xl' => 'modal-xl',
    ][$maxWidth];
    $id = 'modal-' . ($name ?? uniqid());
@endphp

{{-- Bootstrap modal wrapper. This component listens for the same window events
     used by the Alpine modal (open-modal / close-modal) and shows/hides the
     Bootstrap modal via the bootstrap.Modal API so existing usages don't need
     to change. --}}
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered {{ $dialogSize }}">
        <div class="modal-content border-0 shadow-lg">
            {{ $slot }}
        </div>
    </div>
</div>

<script>
    (function() {
        // Wait until Bootstrap is available and DOM loaded
        function init() {
            var el = document.getElementById('{{ $id }}');
            if (!el) return;

            // Create Modal instance
            var modal = null;
            try {
                modal = new bootstrap.Modal(el, {
                    backdrop: true
                });
            } catch (e) {
                // bootstrap not available yet; try again later
                return setTimeout(init, 50);
            }

            // Listen to the same custom events used by the Alpine implementation
            window.addEventListener('open-modal', function(e) {
                if (e.detail == '{{ $name }}') modal.show();
            });

            window.addEventListener('close-modal', function(e) {
                if (e.detail == '{{ $name }}') modal.hide();
            });

            // If server rendered prop says show, display immediately
            @if ($show)
                modal.show();
            @endif
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
</script>
