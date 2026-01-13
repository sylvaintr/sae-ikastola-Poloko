<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\ProfileController;

class ProfileControllerValidateDocxZipUnitTest extends TestCase
{
    public function test_validateDocxZip_returns_false_when_open_fails()
    {
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

        $result = $ref->invoke($controller, $fakeFile);

        @unlink($tmp);

        $this->assertFalse($result);
    }

    public function test_validateDocxZip_returns_false_when_no_word_folder()
    {
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

        $result = $ref->invoke($controller, $fakeFile);

        @unlink($tmp);

        $this->assertFalse($result);
    }

    public function test_validateDocxZip_returns_true_when_word_folder_present()
    {
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

        $result = $ref->invoke($controller, $fakeFile);

        @unlink($tmp);

        $this->assertTrue($result);
    }
}
