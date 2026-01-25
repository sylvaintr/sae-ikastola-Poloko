@php
    $bg = match ($color ?? 'primary') {
        'success' => '#28a745',
        'error' => '#dc3545',
        default => '#f29201', // charte
    };
@endphp

<table align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:18px 0;">
    <tr>
        <td align="center">
            <table cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td
                        style="border-radius:8px; background-color:{{ $bg }}; box-shadow:0 6px 18px rgba(0,0,0,0.08);">
                        <a href="{{ $url }}" target="_blank" rel="noopener"
                            style="display:inline-block; padding:12px 24px; color:#ffffff; text-decoration:none; font-weight:700; font-size:16px; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, Helvetica, sans-serif;">
                            {{ $slot }}
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
