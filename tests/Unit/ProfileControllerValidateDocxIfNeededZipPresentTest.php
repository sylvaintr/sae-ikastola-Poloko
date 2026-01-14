<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\ProfileController;

class ProfileControllerValidateDocxIfNeededZipPresentTest extends TestCase
{
    public function test_validate_docx_si_necessaire_delegue_a_validate_docx_zip_si_ziparchive_disponible()
    {
        // given
        $controller = new ProfileController();
        $ref = new \ReflectionMethod(ProfileController::class, 'validateDocxIfNeeded');
        $ref->setAccessible(true);

        // Ensure the controller's namespace sees class_exists as true
        if (!function_exists('App\\Http\\Controllers\\class_exists')) {
            eval('namespace App\\Http\\Controllers; function class_exists($c){ return true; }');
        }

        $tmp = tempnam(sys_get_temp_dir(), 'docx_present_');

        // If ZipArchive exists, create a real zip with word/; otherwise stub the global ZipArchive
        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive();
            $zip->open($tmp, \ZipArchive::OVERWRITE | \ZipArchive::CREATE);
            $zip->addFromString('word/document.xml', '<xml/>');
            $zip->close();
        } else {
            eval('class ZipArchive { public $numFiles = 1; public function open($p){ return true; } public function getNameIndex($i){ return "word/document.xml"; } public function close(){} }');
            file_put_contents($tmp, 'PK');
        }

        $fakeFile = new class($tmp) {
            private $path; public function __construct($p){ $this->path = $p; } public function getRealPath(){ return $this->path; }
        };

        // when
        $hex = '504b0304ff'; // ZIP magic bytes prefix
        $result = $ref->invoke($controller, $fakeFile, 'docx', $hex, false);

        // then
        @unlink($tmp);
        $this->assertTrue($result, 'Expected validateDocxIfNeeded to delegate to validateDocxZip when ZipArchive available');
    }
}
