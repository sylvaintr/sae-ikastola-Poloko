@php
    $appName = config('app.name', 'Ikastola Poloko');
    $appUrl = config('app.url');
    $contactEmail = config('mail.from.address', 'contact@ikastola-poloko.fr');
@endphp

<tr>
    <td style="padding:18px 22px 22px; text-align:left; background:#ffffff;">
        <hr style="border:none; border-top:1px solid #f1f1f1; margin:0 0 14px;">
        <p
            style="margin:0 0 8px; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, Helvetica, sans-serif; font-size:12px; color:#6b7280; line-height:1.45;">
            Cet e-mail a été envoyé automatiquement par <strong style="color:#111827;">{{ $appName }}</strong>.
            Merci de ne pas y répondre.
        </p>
        <p
            style="margin:0 0 8px; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, Helvetica, sans-serif; font-size:12px; color:#6b7280; line-height:1.45;">
            Assistance : <a href="mailto:{{ $contactEmail }}"
                style="color:#f29201; text-decoration:none; font-weight:600;">{{ $contactEmail }}</a>
            · Site : <a href="{{ $appUrl }}"
                style="color:#f29201; text-decoration:none; font-weight:600;">{{ $appUrl }}</a>
        </p>
        <p
            style="margin:0; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, Helvetica, sans-serif; font-size:11px; color:#9ca3af; line-height:1.45;">
            Données personnelles : ce message est lié à votre compte. Si vous n’êtes pas à l’origine de cette demande,
            vous pouvez ignorer cet e-mail.
        </p>
        <p
            style="margin:10px 0 0; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, Helvetica, sans-serif; font-size:11px; color:#9ca3af;">
            © {{ date('Y') }} {{ $appName }} — Tous droits réservés.
        </p>
    </td>
</tr>
