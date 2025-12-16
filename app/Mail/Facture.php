<?php

namespace App\Mail;

use App\Models\Famille;
use App\Models\Facture as FactureModel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Facture extends Mailable
{
    use Queueable, SerializesModels;

    public FactureModel $facture;
    public Famille $famille;

    /**
     * Constructeur pour initialiser une nouvelle instance du message.
     */
    public function __construct(FactureModel $facture, Famille $famille)
    {
        $this->facture = $facture;
        $this->famille = $famille;
    }


    /**
     * methode pour definir l'enveloppe du mail
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre facture Ikastola',
        );
    }

    /**
     * methode pour definir le contenu du mail
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content(): Content
    {

        return new Content(
            view: 'facture.mail',
            with: [
                'facture' => $this->facture,
                'famille' => $this->famille,
                'companyEmail' => config('mail.from.address'),
                'companyName' => config('mail.from.name'),
            ],

        );
    }

    /**
     * methode pour definir les pieces jointes du mail
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
