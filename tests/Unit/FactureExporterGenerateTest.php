<?php
namespace Tests\Unit;

use App\Models\Facture;
use App\Models\Utilisateur;
use App\Services\FactureExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FactureExporterGenerateTest extends TestCase
{
    use RefreshDatabase;

    public function test_generateFactureToWord_creates_docx_and_calls_conversion()
    {
        if (! class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive not available.');
        }

        Storage::fake('public');

        // create a minimal, valid DOCX (zip) template so TemplateProcessor can open it
        $templateDir = storage_path('app/templates');
        if (! file_exists($templateDir)) {mkdir($templateDir, 0755, true);}
        $templatePath = $templateDir . '/facture_template.docx';

        $zip = new \ZipArchive();
        $zip->open($templatePath, \ZipArchive::OVERWRITE | \ZipArchive::CREATE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>');
        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>Template</w:t></w:r></w:p></w:body></w:document>');
        $zip->close();

        // create facture and related utilisateur
        $user    = Utilisateur::factory()->create(['nom' => 'Dupont']);
        $facture = Facture::factory()->create(['idUtilisateur' => $user->idUtilisateur, 'previsionnel' => false, 'dateC' => now(), 'idFamille' => 0]);
        $facture->setRelation('utilisateur', $user);

        // mock conversion service to avoid calling libreoffice
        $mockConv = $this->getMockBuilder(\App\Services\FactureConversionService::class)->onlyMethods(['convertFactureToPdf'])->getMock();
        $mockConv->expects($this->once())->method('convertFactureToPdf')->with($this->equalTo($facture));
        $this->app->instance(\App\Services\FactureConversionService::class, $mockConv);

        // ensure output dir exists
        $outputDir = storage_path('app/public/factures/');
        if (! file_exists($outputDir)) {mkdir($outputDir, 0755, true);}

        $exporter = new FactureExporter();
        $exporter->generateFactureToWord($facture);

        $this->assertFileExists($outputDir . 'facture-' . $facture->idFacture . '.docx');

        // cleanup
        @unlink($templatePath);
        @unlink($outputDir . 'facture-' . $facture->idFacture . '.docx');
    }
}
