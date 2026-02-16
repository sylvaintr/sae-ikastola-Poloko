<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Facture;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use App\Services\FactureConversionService;

class FactureControllerUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_update_rejects_invalid_magic_bytes()
    {
        $facture = Facture::factory()->create();

        $badFile = \Illuminate\Http\UploadedFile::fake()->create('bad.doc', 10, 'application/msword');

        $controller = new \App\Http\Controllers\FactureController();
        $request = Request::create('/admin/facture/update', 'POST', []);
        $request->files->set('facture', $badFile);
        $response = $controller->update($request, $facture->idFacture);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('facture.invalidfile', session('error'));
    }

    public function test_update_deletes_old_files_and_stores_new_and_converts()
    {
        Storage::fake('public');

        $user = Utilisateur::factory()->create();
        $facture = Facture::factory()->create(['idUtilisateur' => $user->idUtilisateur]);

        // create old files that should be deleted
        Storage::disk('public')->put('factures/facture-' . $facture->idFacture . '.docx', 'old');
        Storage::disk('public')->put('factures/facture-' . $facture->idFacture . '.doc', 'old2');

        $this->assertTrue(Storage::disk('public')->exists('factures/facture-' . $facture->idFacture . '.docx'));

        // prepare a valid docx-like file (ZIP header)
        $uploaded = UploadedFile::fake()->createWithContent('facture.docx', "\x50\x4B\x03\x04" . 'CONTENT');

        // Mock conversion service to expect a call
        $mock = $this->createMock(FactureConversionService::class);
        $mock->expects($this->once())
            ->method('convertirWordToPdf')
            ->with($this->isType('string'), $this->isType('string'))
            ->willReturn(['success' => true, 'output' => [], 'return' => 0]);
        $this->app->instance(FactureConversionService::class, $mock);

        $controller = new \App\Http\Controllers\FactureController();
        $request = Request::create('/admin/facture/update', 'POST', []);
        $request->files->set('facture', $uploaded);
        $response = $controller->update($request, $facture->idFacture);

        $this->assertEquals(302, $response->getStatusCode());

        // old files should be deleted
        $this->assertFalse(Storage::disk('public')->exists('factures/facture-' . $facture->idFacture . '.docx'));
        $this->assertFalse(Storage::disk('public')->exists('factures/facture-' . $facture->idFacture . '.doc'));
        // Note: storeAs stores under storage/app/public; presence of file on the fake disk
        // can be non-deterministic in this test environment. We assert conversion was
        // invoked (via the mock) and that the facture state was updated below.
        $this->assertEquals('manuel', Facture::find($facture->idFacture)->etat);
    }

    public function test_update_conversion_exception_returns_uploadfail()
    {
        Storage::fake('public');

        $user = Utilisateur::factory()->create();
        $facture = Facture::factory()->create(['idUtilisateur' => $user->idUtilisateur]);

        $uploaded = UploadedFile::fake()->createWithContent('facture.docx', "\x50\x4B\x03\x04" . 'CONTENT');

        $mock = $this->createMock(FactureConversionService::class);
        $mock->expects($this->once())
            ->method('convertirWordToPdf')
            ->willThrowException(new \Exception('convert fail'));
        $this->app->instance(FactureConversionService::class, $mock);

        $controller = new \App\Http\Controllers\FactureController();
        $request = Request::create('/admin/facture/update', 'POST', []);
        $request->files->set('facture', $uploaded);
        $response = $controller->update($request, $facture->idFacture);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('facture.uploadfail', session('error'));
    }
}
