<?php
namespace App\Mail;

use App\Models\Facture as FactureModel;
use App\Models\Utilisateur;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Facture extends Mailable
{
    use Queueable, SerializesModels;

    public FactureModel $facture;
    public Utilisateur $utilisateur;

    /**
     * Constructeur pour initialiser une nouvelle instance du message.
     */
    public function __construct(FactureModel $facture, Utilisateur $utilisateur)
    {
        $this->facture     = $facture;
        $this->utilisateur = $utilisateur;
    }

    /**
     * Méthode pour definir l'enveloppe du mail
     * @return \Illuminate\Mail\Mailables\Envelope L'enveloppe du mail, contenant des informations telles que le sujet du mail
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('facture.email_subject', ['company' => config('mail.from.name')]),
        );
    }

    /**
     * Méthode pour definir le contenu du mail
     * @return \Illuminate\Mail\Mailables\Content Le contenu du mail, spécifiant la vue à utiliser et les données à passer à cette vue pour générer le corps du mail
     */
    public function content(): Content
    {

        return new Content(
            view: 'facture.mail',
            with: [
                'facture'      => $this->facture,
                'utilisateur'  => $this->utilisateur,
                'companyEmail' => config('mail.from.address'),
                'companyName'  => config('mail.from.name'),
            ],

        );
    }

    /**
     * Méthode pour definir les pieces jointes du mail
     * @return array<int, \Illuminate\Mail\Mailables\Attachment> Un tableau des pièces jointes à inclure dans le mail. Dans ce cas, il n'y a pas de pièces jointes, donc le tableau est vide.
     */
    public function attachments(): array
    {
        return [];
    }
}
