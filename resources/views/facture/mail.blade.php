<!DOCTYPE html>
<html lang="fr">

<head>
    <title>Facture n° {{ $facture->idFacture ?? '—' }}</title>
    <meta charset="UTF-8">
</head>

<body>
    <p>Bonjour Madame, Monsieur {{ $famille->utilisateurs()->first()->nom }},</p>

    <p>Veuillez trouver ci-joint la facture <strong>{{ $facture->idFacture ?? '—' }}</strong> datée du
        {{ $facture->dateC->format('d/m/Y') ?? '—' }} .</p>

    <p>Pour toute question, contactez-nous à
        {{ $companyEmail ?? (config('mail.from.address') ?? 'contact@votre-entreprise.tld') }}.</p>

    <p>Cordialement,<br>{{ $companyName ?? 'Votre Entreprise' }}</p>

</body>

</html>
