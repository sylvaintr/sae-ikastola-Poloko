<?php
namespace Tests\Unit;

use App\Models\Facture;
use Tests\TestCase;

class FactureExporterCloneRowAndAbortTest extends TestCase
{
    public function test_generateFactureToWord_cloneRow_succeeds_with_template_variable()
    {
        $templatePath = storage_path('app/templates/facture_template.docx');
        @mkdir(dirname($templatePath), 0755, true);

        // create a template that contains the montantreg placeholder used by cloneRow
        $tmp = sys_get_temp_dir() . '/tpl_' . uniqid() . '.docx';
        $zip = new \ZipArchive();
        $zip->open($tmp, \ZipArchive::CREATE);
        $zip->addFromString('word/document.xml', '<w:document>${montantreg#1}</w:document>');
        $zip->close();
        copy($tmp, $templatePath);
        @unlink($tmp);

        // ensure output dir removed to test mkdir
        $outdir = storage_path('app/public/factures/');
        if (file_exists($outdir)) {
            \Illuminate\Support\Facades\File::deleteDirectory($outdir);
        }

        $mockCalc = new class {
            public function calculerMontantFacture(string $id)
            {
                return ['nbEnfants' => 0, 'montantcotisation' => 0, 'montantparticipation' => 0, 'totalPrevisionnel' => 0];
            }
            public function calculerRegularisation($id)
            {return 0;}
        };
        $this->app->instance('App\\Services\\FactureCalculator', $mockCalc);

        $facture               = new Facture();
        $facture->idFacture    = 3333;
        $facture->previsionnel = true;
        $facture->etat         = 'draft';
        $facture->dateC        = new \DateTime();
        $user                  = new class {public $nom = 'T';public function familles()
            {return new class {public function where()
                    {return new class {public function first()
                            {return null;}};}};}};
        $facture->utilisateur  = $user;

        $svc = app()->make('App\\Services\\FactureExporter');
        $svc->generateFactureToWord($facture);

        $this->assertDirectoryExists($outdir);
    }

    public function test_generateFactureToWord_aborts_when_template_missing()
    {
        $templatePath = storage_path('app/templates/facture_template.docx');
        if (file_exists($templatePath)) {
            unlink($templatePath);
        }

        $facture               = new Facture();
        $facture->idFacture    = 4444;
        $facture->previsionnel = false;
        $facture->etat         = 'draft';
        $facture->dateC        = new \DateTime();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $svc = app()->make('App\\Services\\FactureExporter');
        $svc->generateFactureToWord($facture);
    }
}
