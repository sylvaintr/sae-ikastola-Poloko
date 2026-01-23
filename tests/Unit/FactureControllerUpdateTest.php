<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Facture;
use Illuminate\Http\Request;

class FactureControllerUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_mise_a_jour_retourne_tot_si_magic_bytes_invalide_et_ne_change_pas_etat()
    {
        // given
        $facture = Facture::factory()->create(['etat' => 'brouillon']);
        $invalid = UploadedFile::fake()->createWithContent('invalid.doc', 'NOT_MAGIC_BYTES');
        $request = Request::create('/', 'POST');
        $request->files->set('facture', $invalid);

        // when
        $controller = new \App\Http\Controllers\FactureController();
        $response = $controller->update($request, (string)$facture->idFacture);

        // then
        $facture->refresh();
        $this->assertEquals('brouillon', $facture->etat);
    }

    public function test_mise_a_jour_avec_magic_bytes_valide_definit_etat_manuel_et_enregistre()
    {
        // given
        $facture = Facture::factory()->create(['etat' => 'brouillon']);

        // prepare a file that starts with OLE header (doc) so the magic-bytes check passes
        $oleHeader = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";
        $valid = UploadedFile::fake()->createWithContent('facture.doc', $oleHeader . 'REST_OF_FILE');

        // Mock the public disk to avoid touching the real filesystem and to prevent exec conversion
        $disk = \Mockery::mock();
        $disk->shouldReceive('exists')->andReturn(false);
        $disk->shouldReceive('delete')->andReturnNull();
        $disk->shouldReceive('putFileAs')->andReturn('factures/facture-' . $facture->idFacture . '.doc');
        $disk->shouldReceive('path')->andReturn('/nonexistent/path/to/output');
        $disk->shouldReceive('url')->andReturn('/storage/factures/facture-' . $facture->idFacture . '.pdf');

        Storage::shouldReceive('disk')->with('public')->andReturn($disk);

        $request = Request::create('/', 'POST');
        $request->files->set('facture', $valid);

        // when
        $controller = new \App\Http\Controllers\FactureController();
        $response = $controller->update($request, (string)$facture->idFacture);

        // then
        $facture->refresh();
        $this->assertEquals('manuel', $facture->etat);
    }

    public function test_mise_a_jour_supprime_fichiers_existants_si_present()
    {
        // given
        $facture = Facture::factory()->create(['etat' => 'brouillon']);

        $oleHeader = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";
        $valid = UploadedFile::fake()->createWithContent('facture.doc', $oleHeader . 'REST_OF_FILE');

        $disk = \Mockery::mock();
        $disk->shouldReceive('exists')->andReturn(true);
        $disk->shouldReceive('delete')->times(3)->with(\Mockery::on(function ($arg) use ($facture) {
            return strpos($arg, 'factures/facture-' . $facture->idFacture) === 0;
        }))->andReturnNull();
        $disk->shouldReceive('putFileAs')->andReturn('factures/facture-' . $facture->idFacture . '.doc');
        $disk->shouldReceive('path')->andReturn('/nonexistent/path/to/output');
        $disk->shouldReceive('url')->andReturn('/storage/factures/facture-' . $facture->idFacture . '.pdf');

        Storage::shouldReceive('disk')->with('public')->andReturn($disk);

        $request = Request::create('/', 'POST');
        $request->files->set('facture', $valid);

        // when
        $controller = new \App\Http\Controllers\FactureController();
        $response = $controller->update($request, (string)$facture->idFacture);

        // then
        $facture->refresh();
        $this->assertEquals('manuel', $facture->etat);
    }

    public function test_mise_a_jour_appelle_exec_quand_fichier_entree_existe()
    {
        // given
        $facture = Facture::factory()->create(['etat' => 'brouillon']);

        $oleHeader = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";
        $valid = UploadedFile::fake()->createWithContent('facture.doc', $oleHeader . 'REST_OF_FILE');

        // create a real temporary input file so file_exists($inputPath) is true
        $inputPath = sys_get_temp_dir() . '/facture_input_' . uniqid() . '.doc';
        file_put_contents($inputPath, 'dummy');

        // prepare a log file to capture calls to exec
        $execLog = sys_get_temp_dir() . '/libreoffice_exec_' . uniqid() . '.log';

        // define a namespaced exec stub used by the controller
        if (!function_exists('App\\Http\\Controllers\\exec')) {
            eval('namespace App\\Http\\Controllers; function exec($cmd) { file_put_contents("' . addslashes($execLog) . '", $cmd.PHP_EOL, FILE_APPEND); }');
        }

        $disk = \Mockery::mock();
        $disk->shouldReceive('exists')->andReturn(false);
        $disk->shouldReceive('delete')->andReturnNull();
        $disk->shouldReceive('putFileAs')->andReturn('factures/facture-' . $facture->idFacture . '.doc');
        $disk->shouldReceive('path')->andReturn($inputPath);
        $disk->shouldReceive('url')->andReturn('/storage/factures/facture-' . $facture->idFacture . '.pdf');

        Storage::shouldReceive('disk')->with('public')->andReturn($disk);

        $request = Request::create('/', 'POST');
        $request->files->set('facture', $valid);

        // when
        $controller = new \App\Http\Controllers\FactureController();
        $response = $controller->update($request, (string)$facture->idFacture);

        // then
        $this->assertFileExists($inputPath);
        $this->assertFileExists($execLog);
        $log = file_get_contents($execLog);
        $this->assertStringContainsString('libreoffice', $log);

        // cleanup
        @unlink($inputPath);
        @unlink($execLog);
    }
}
