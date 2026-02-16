<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\ProfileController;

class ProfileControllerValidateDocxZipTest extends TestCase
{
    public function test_validate_docx_si_necessaire_utilise_branche_ziparchive_quand_disponible()
    {
        // given
        $controller = new ProfileController();

        $ref = new \ReflectionMethod(ProfileController::class, 'validateDocxIfNeeded');
        $ref->setAccessible(true);

        $tmp = tempnam(sys_get_temp_dir(), 'docx');

        // Ensure we have a ZipArchive available for the controller branch.
        if (!class_exists('ZipArchive')) {
            // Provide a minimal stub so the controller's ZipArchive usage succeeds.
            eval('class ZipArchive { public $numFiles = 1; public function open($p){return true;} public function getNameIndex($i){return "word/document.xml";} public function close(){} }');
            file_put_contents($tmp, 'PK');
        } else {
            // Create a real zip containing a word/ entry so validateDocxZip finds it.
            $zip = new \ZipArchive();
            $zip->open($tmp, \ZipArchive::CREATE);
            $zip->addFromString('word/document.xml', '<xml/>');
            $zip->close();
        }

        $fakeFile = new class($tmp) {
            private $path;
            public function __construct($p) { $this->path = $p; }
            public function getRealPath() { return $this->path; }
        };

        // when
        // Hex starting with ZIP magic bytes (504b0304) and indicate not yet valid
        $hex = '504b03040000';
        $result = $ref->invoke($controller, $fakeFile, 'docx', $hex, false);

        // then
        @unlink($tmp);

        $this->assertTrue($result, 'Expected validateDocxIfNeeded to return true when ZipArchive branch finds word/');
    }
}
