<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\ProfileController;

class ProfileControllerPrivateTest extends TestCase
{
    use RefreshDatabase;

    public function test_obtenir_et_verifier_magic_bytes()
    {
        // given
        $controller = new ProfileController();

        $getMagic = new \ReflectionMethod($controller, 'getMagicBytesForExtension');
        $getMagic->setAccessible(true);

        $check = new \ReflectionMethod($controller, 'checkMagicBytes');
        $check->setAccessible(true);

        // when
        $pdfMagic = $getMagic->invoke($controller, 'pdf');
        $checkResult1 = $check->invoke($controller, '25504446abcdef', ['25504446']);
        $checkResult2 = $check->invoke($controller, 'abcdef1234', ['25504446']);

        // then
        $this->assertIsArray($pdfMagic);
        $this->assertContains('25504446', $pdfMagic);
        $this->assertTrue($checkResult1);
        $this->assertFalse($checkResult2);
    }

    public function test_valider_extension_fichier_retourne_resultat_attendu()
    {
        // given
        $controller = new ProfileController();
        $method = new \ReflectionMethod($controller, 'validateFileExtension');
        $method->setAccessible(true);

        // when
        $res = $method->invoke($controller, 'pdf');
        $res2 = $method->invoke($controller, 'exe');

        // then
        $this->assertNull($res);
        $this->assertInstanceOf(RedirectResponse::class, $res2);
    }

    public function test_gestion_erreur_upload_supprime_fichier_et_retourne_redirect()
    {
        // given
        Storage::fake('public');
        $path = 'profiles/1/obligatoires/tmp.pdf';
        Storage::disk('public')->put($path, 'content');

        $controller = new ProfileController();
        $method = new \ReflectionMethod($controller, 'handleUploadError');
        $method->setAccessible(true);

        // when
        $response = $method->invoke($controller, ['document' => ['error']], $path);

        // then
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertFalse(Storage::disk('public')->exists($path));
        $this->assertTrue(session()->has('error'));
    }
}
