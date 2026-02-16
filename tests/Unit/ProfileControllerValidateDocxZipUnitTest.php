<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\ProfileController;

class ProfileControllerValidateDocxZipUnitTest extends TestCase
{
    public function test_validate_docx_zip_retourne_false_si_ouverture_echoue()
    {
        // given
        $controller = new ProfileController();
        $ref = new \ReflectionMethod(ProfileController::class, 'validateDocxZip');
        $ref->setAccessible(true);

        $tmp = tempnam(sys_get_temp_dir(), 'bad_zip_');
        file_put_contents($tmp, 'this is not a zip');

        // If ZipArchive doesn't exist, stub it so open() returns false for this filename
        if (!class_exists('ZipArchive')) {
            eval('class ZipArchive { public $numFiles = 0; public function open($p){ return false; } public function close(){} public function getNameIndex($i){return "";} }');
        }

        $fakeFile = new class($tmp) {
            private $path; public function __construct($p){ $this->path = $p; } public function getRealPath(){ return $this->path; }
        };

        // when
        $result = $ref->invoke($controller, $fakeFile);

        // then
        @unlink($tmp);
        $this->assertFalse($result);
    }

    public function test_validate_docx_zip_retourne_false_si_pas_de_dossier_word()
    {
        // given
        $controller = new ProfileController();
        $ref = new \ReflectionMethod(ProfileController::class, 'validateDocxZip');
        $ref->setAccessible(true);

        $tmp = tempnam(sys_get_temp_dir(), 'no_word_');

        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive();
            $zip->open($tmp, \ZipArchive::OVERWRITE | \ZipArchive::CREATE);
            $zip->addFromString('docProps/core.xml', '<xml/>');
            $zip->close();
        } else {
            // stub that returns a single entry not starting with 'word/'
            eval('class ZipArchive { public $numFiles = 1; public function open($p){ return true; } public function getNameIndex($i){ return "docProps/core.xml"; } public function close(){} }');
        }

        $fakeFile = new class($tmp) {
            private $path; public function __construct($p){ $this->path = $p; } public function getRealPath(){ return $this->path; }
        };

        // when
        $result = $ref->invoke($controller, $fakeFile);

        // then
        @unlink($tmp);
        $this->assertFalse($result);
    }

    public function test_validate_docx_zip_retourne_true_si_dossier_word_present()
    {
        // given
        $controller = new ProfileController();
        $ref = new \ReflectionMethod(ProfileController::class, 'validateDocxZip');
        $ref->setAccessible(true);

        $tmp = tempnam(sys_get_temp_dir(), 'has_word_');

        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive();
            $zip->open($tmp, \ZipArchive::OVERWRITE | \ZipArchive::CREATE);
            $zip->addFromString('word/document.xml', '<xml/>');
            $zip->close();
        } else {
            // stub that returns an entry starting with 'word/'
            eval('class ZipArchive { public $numFiles = 1; public function open($p){ return true; } public function getNameIndex($i){ return "word/document.xml"; } public function close(){} }');
        }

        $fakeFile = new class($tmp) {
            private $path; public function __construct($p){ $this->path = $p; } public function getRealPath(){ return $this->path; }
        };

        // when
        $result = $ref->invoke($controller, $fakeFile);

        // then
        @unlink($tmp);
        $this->assertTrue($result);
    }
}
