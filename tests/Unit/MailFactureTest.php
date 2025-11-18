<?php

namespace Tests\Unit;

use App\Mail\Facture as FactureMail;
use App\Models\Facture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailFactureTest extends TestCase
{
    use RefreshDatabase;

    public function test_facture_mail_builds(): void
    {
        $facture = Facture::factory()->create();
        $famille = $facture->famille()->first();

        $mail = new FactureMail($facture, $famille);

        $rendered = $mail->render();
        $this->assertIsString($rendered);
    }
}
