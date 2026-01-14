<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\RedirectResponse;

class ProfileControllerValidateMagicBytesTest extends TestCase
{
    use RefreshDatabase;

    public function test_validateFileMagicBytesWrapper_retourne_redirect_si_invalide()
    {
        // given
        // none

        // when

        // then
        $controller = new \App\Http\Controllers\ProfileController();

        $bad = UploadedFile::fake()->createWithContent('bad.pdf', 'NOT_PDF_BYTES');

        $rm = new \ReflectionMethod($controller, 'validateFileMagicBytesWrapper');
        $rm->setAccessible(true);

        $result = $rm->invoke($controller, $bad, 'pdf');

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $session = $result->getSession();
        $this->assertNotNull($session->get('error'));
    }
}
