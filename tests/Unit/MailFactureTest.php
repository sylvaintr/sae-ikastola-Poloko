<?php
namespace Tests\Unit;

use App\Mail\Facture as FactureMail;
use App\Models\Facture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailFactureTest extends TestCase
{
    use RefreshDatabase;

    public function test_construction_du_mail_facture(): void
    {
        // given
        $facture     = Facture::factory()->create();
        $utilisateur = $facture->utilisateur()->first();

        // when
        $mail = new FactureMail($facture, $utilisateur);

        // then

        $rendered = $mail->render();
        $this->assertIsString($rendered);
    }
}
