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

    public function test_get_magic_bytes_and_check_magic_bytes()
    {
        $controller = new ProfileController();

        $getMagic = new \ReflectionMethod($controller, 'getMagicBytesForExtension');
        $getMagic->setAccessible(true);
        $pdfMagic = $getMagic->invoke($controller, 'pdf');
        $this->assertIsArray($pdfMagic);
        $this->assertContains('25504446', $pdfMagic);

        $check = new \ReflectionMethod($controller, 'checkMagicBytes');
        $check->setAccessible(true);
        $this->assertTrue($check->invoke($controller, '25504446abcdef', ['25504446']));
        $this->assertFalse($check->invoke($controller, 'abcdef1234', ['25504446']));
    }

    public function test_validate_file_extension_returns_expected()
    {
        $controller = new ProfileController();
        $method = new \ReflectionMethod($controller, 'validateFileExtension');
        $method->setAccessible(true);

        $res = $method->invoke($controller, 'pdf');
        $this->assertNull($res);

        $res2 = $method->invoke($controller, 'exe');
        $this->assertInstanceOf(RedirectResponse::class, $res2);
    }

    public function test_handle_upload_error_deletes_file_and_returns_redirect()
    {
        Storage::fake('public');
        $path = 'profiles/1/obligatoires/tmp.pdf';
        Storage::disk('public')->put($path, 'content');

        $controller = new ProfileController();
        $method = new \ReflectionMethod($controller, 'handleUploadError');
        $method->setAccessible(true);

        $response = $method->invoke($controller, ['document' => ['error']], $path);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertFalse(Storage::disk('public')->exists($path));
        $this->assertTrue(session()->has('error'));
    }
}
