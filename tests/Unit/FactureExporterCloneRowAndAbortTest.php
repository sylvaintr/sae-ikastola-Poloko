<?php
namespace Tests\Unit;

use App\Models\Facture;
use Tests\TestCase;

class FactureExporterCloneRowAndAbortTest extends TestCase
{
    public function test_generateFactureToWord_cloneRow_succeeds_with_template_variable()
    {
        if (! class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive not available.');
        }

        if (! class_exists(\PhpOffice\PhpWord\TemplateProcessor::class)) {
            $this->markTestSkipped('PhpOffice\\PhpWord\\TemplateProcessor not available.');
        }

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

    public function test_generateFactureToWord_handles_missing_template_gracefully()
    {
        if (! class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive not available.');
        }

        if (! class_exists(\PhpOffice\PhpWord\TemplateProcessor::class)) {
            $this->markTestSkipped('PhpOffice\\PhpWord\\TemplateProcessor not available.');
        }

        // Le template est recréé par TestCase::setUp(), donc on le supprime ici
        $templatePath = storage_path('app/templates/facture_template.docx');
        if (file_exists($templatePath)) {
            unlink($templatePath);
        }
        // S'assurer que le répertoire du template ne contient pas d'autre fichier template
        $templateDir = dirname($templatePath);
        if (file_exists($templateDir . '/facture_template.docx')) {
            unlink($templateDir . '/facture_template.docx');
        }

        $facture               = new Facture();
        $facture->idFacture    = 4444;
        $facture->previsionnel = false;
        $facture->etat         = 'draft';
        $facture->dateC        = new \DateTime();

        // Le service gère l'erreur gracieusement via handleTemplateError()
        // et retourne null au lieu de laisser l'exception remonter
        $svc = app()->make('App\\Services\\FactureExporter');
        $result = $svc->generateFactureToWord($facture);

        // Restaurer le template pour les autres tests
        if (! file_exists($templatePath)) {
            @mkdir(dirname($templatePath), 0755, true);
            $zip = new \ZipArchive();
            if ($zip->open($templatePath, \ZipArchive::OVERWRITE | \ZipArchive::CREATE) === true) {
                $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"></Types>');
                $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>');
                $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>Template</w:t></w:r></w:p></w:body></w:document>');
                $zip->close();
            }
        }

        // La méthode gère l'erreur en interne et retourne null
        $this->assertNull($result, 'La méthode devrait retourner null quand le template est manquant');
    }
}
