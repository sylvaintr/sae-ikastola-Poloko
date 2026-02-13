<?php
namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        if (method_exists($this, 'withoutVite')) {
            $this->withoutVite();
        }

        // Disable CSRF verification in tests to avoid 419 responses
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        try {
            $token = bin2hex(random_bytes(16));
        } catch (\Exception $e) {
            $token = 'testingtoken';
        }

        $this->withSession(['_token' => $token]);
        $this->withHeaders([
            'X-CSRF-TOKEN' => $token,
            'X-XSRF-TOKEN' => $token,
        ]);

        // Ensure a minimal facture_template.docx exists for tests that rely on it
        $templateDir  = storage_path('app/templates');
        $templatePath = $templateDir . '/facture_template.docx';
        if (! file_exists($templatePath)) {
            if (! file_exists($templateDir)) {
                @mkdir($templateDir, 0755, true);
            }
            if (class_exists(\ZipArchive::class)) {
                $zip = new \ZipArchive();
                if ($zip->open($templatePath, \ZipArchive::OVERWRITE | \ZipArchive::CREATE) === true) {
                    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"></Types>');
                    $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>');
                    $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>Template</w:t></w:r></w:p></w:body></w:document>');
                    $zip->close();
                }
            } else {
                @file_put_contents($templatePath, 'DUMMY_DOCX');
            }
        }
    }

}
