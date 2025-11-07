<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Facture n° {{ $facture->idFacture ?? '—' }}</title>
    <meta charset="UTF-8">
</head>
<body>
    <h2>Facture n° {{ $facture->idFacture ?? '—' }}</h2>

    <p>Bonjour {{ $client->nom ?? $client->name ?? 'Madame, Monsieur' }},</p>

    <p>Veuillez trouver ci-joint la facture <strong>{{ $facture->idFacture ?? '—' }}</strong> datée du {{ $facture->date ?? '—' }} .</p>

    <p>Pour toute question, contactez-nous à {{ $companyEmail ?? config('mail.from.address') ?? 'contact@votre-entreprise.tld' }} ou au {{ $companyPhone ?? '—' }}.</p>

    <p>Cordialement,<br>{{ $companyName ?? 'Votre Entreprise' }}</p>
    
</body>
</html>
