<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <title>{{ __('facture.email_title', ['id' => $facture->idFacture ?? '—']) }}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>body{font-family: Arial, Helvetica, sans-serif;}</style>
</head>

<body>
    <p>{{ __('facture.email_greeting', ['name' => $utilisateur->nom]) }}</p>

    <p>{{ __('facture.email_intro', ['id' => $facture->idFacture ?? '—', 'date' => $facture->dateC->format('d/m/Y') ?? '—']) }}</p>

    <p>{{ __('facture.email_contact', ['email' => $companyEmail ?? (config('mail.from.address') ?? 'contact@votre-entreprise.tld')]) }}</p>

    <p>{{ __('mail.salutation') }}<br>{{ $companyName ?? config('mail.from.name') }}</p>

</body>

</html>
