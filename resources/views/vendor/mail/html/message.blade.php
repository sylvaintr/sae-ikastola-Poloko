<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body style="margin:0; padding:0; background-color:#f6f7fb;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f6f7fb; width:100%;">
        <tr>
            <td align="center" style="padding:28px 12px;">
                <table width="600" cellpadding="0" cellspacing="0" role="presentation"
                    style="background:#ffffff; border-radius:12px; border:1px solid #e9ecef; box-shadow:0 20px 45px rgba(15, 23, 42, 0.08); overflow:hidden;">

                    {{-- Header --}}
                    {{ $header ?? '' }}

                    {{-- Body --}}
                    <tr>
                        <td
                            style="padding:28px 28px 12px; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, Helvetica, sans-serif; color:#111827; font-size:16px; line-height:1.65;">
                            {{ $slot }}
                        </td>
                    </tr>

                    {{-- Subcopy --}}
                    @isset($subcopy)
                        <tr>
                            <td
                                style="padding:0 28px 22px; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, Helvetica, sans-serif; color:#6b7280; font-size:13px; line-height:1.55;">
                                <hr style="border:none; border-top:1px solid #f1f1f1; margin:14px 0 14px;">
                                {{ $subcopy }}
                            </td>
                        </tr>
                    @endisset

                    {{-- Footer --}}
                    {{ $footer ?? '' }}
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
