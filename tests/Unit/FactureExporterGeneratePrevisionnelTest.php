<?php
namespace Tests\Unit;

use App\Models\Facture;
use Tests\TestCase;

class FactureExporterGeneratePrevisionnelTest extends TestCase
{
    public function test_generateFactureToWord_handles_previsionnel_false()
    {
        if (! class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive not available.');
        }

        // ensure template exists (TestCase setup should create it, but be explicit)
        $templatePath = storage_path('app/templates/facture_template.docx');
        if (! file_exists($templatePath)) {
            // create a minimal docx zip with word/document.xml
            $tmp = sys_get_temp_dir() . '/tpl_' . uniqid() . '.docx';
            $zip = new \ZipArchive();
            $zip->open($tmp, \ZipArchive::CREATE);
            $zip->addFromString('word/document.xml', '<w:document></w:document>');
            $zip->close();
            @mkdir(dirname($templatePath), 0755, true);
            copy($tmp, $templatePath);
            @unlink($tmp);
        }

        // ensure output dir missing to hit mkdir branch
        $outdir = storage_path('app/public/factures/');
        if (file_exists($outdir)) {
            \Illuminate\Support\Facades\File::deleteDirectory($outdir);
        }

        // bind a simple facture calculator
        $mockCalc = new class {
            public function calculerMontantFacture(string $id)
            {
                return ['nbEnfants' => 0, 'montantcotisation' => 0, 'montantparticipation' => 0, 'totalPrevisionnel' => 0];
            }
            public function calculerRegularisation($id)
            {return 0;}
        };
        $this->app->instance('App\Services\FactureCalculator', $mockCalc);

        $facture               = new Facture();
        $facture->idFacture    = 7777;
        $facture->previsionnel = false;
        $facture->etat         = 'draft';
        $facture->dateC        = new \DateTime();

        // relations used in code
        $user = new class {
            public $nom = 'Test';
            public function familles()
            {return new class {public function where()
                    {return new class {public function first()
                            {return null;}};}};}
        };
        $facture->utilisateur = $user;

        // call method, should run without exception and create output dir
        $svc = app()->make('App\Services\FactureExporter');
        $svc->generateFactureToWord($facture);

        $this->assertDirectoryExists($outdir);
    }
}
