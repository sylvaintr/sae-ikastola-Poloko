<a href="{{ route('facture.show', $facture->idFacture) }}" class="text-decoration-none text-black"><i
        class="bi bi-eye me-3"></i></a>

@if (!$facture->etat)
    <i class="bi bi-pencil-fill me-3"></i>
    <a href="{{ route('facture.valider', $facture->idFacture) }}" class="text-decoration-none text-black"><i
            class="bi bi-check-lg me-3"></i></a>
@else
    <a href="{{ route('facture.envoyer', $facture->idFacture) }}" class="text-decoration-none text-black"><i
            class="bi bi-send-fill me-3"></i></a>
@endif

<a href="{{ route('facture.export', $facture->idFacture) }}" class="text-decoration-none text-black"><i
        class="bi bi-download me-3"></i></a>
