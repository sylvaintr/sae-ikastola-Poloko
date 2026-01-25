@php
    $appUrl = config('app.url');
    $appName = config('app.name', 'Ikastola Poloko');
    $logoUrl = asset('images/logo-mail.png');
@endphp

<tr>
    <td style="padding:18px 22px; text-align:left; background:#ffffff;">
        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td style="vertical-align:middle;">
                    <a href="{{ $appUrl }}" style="text-decoration:none;">
                        <img src="{{ $logoUrl }}" alt="{{ $appName }}" width="140"
                            style="display:block; border:0; outline:none; text-decoration:none; max-width:140px;">
                    </a>
                </td>
                <td align="right" style="vertical-align:middle;">
                    <span
                        style="display:inline-block; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, Helvetica, sans-serif; font-weight:700; color:#111827; font-size:14px;">
                        {{ $appName }}
                    </span>
                </td>
            </tr>
        </table>
        <div style="height:10px;"></div>
        <div style="height:3px; background-color:#f29201; border-radius:999px;"></div>
    </td>
</tr>
