<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\ProfileController;

class ProfileControllerValidateDocxIfNeededNoZipTest extends TestCase
{
    public function test_validateDocxIfNeeded_returns_true_when_no_ZipArchive_class()
    {
        $controller = new ProfileController();

        $ref = new \ReflectionMethod(ProfileController::class, 'validateDocxIfNeeded');
        $ref->setAccessible(true);

        // Ensure the namespaced override of class_exists is not already defined
        if (!function_exists('App\\Http\\Controllers\\class_exists')) {
            eval('namespace App\\Http\\Controllers; function class_exists($c){ return false; }');
        }

        // Provide a hex that starts with ZIP magic bytes
        $hex = '504b0304abcdef';

        $tmp = tempnam(sys_get_temp_dir(), 'docx_nozip_');
        file_put_contents($tmp, 'PK');

        $fakeFile = new class($tmp) {
            private $path; public function __construct($p){ $this->path = $p; } public function getRealPath(){ return $this->path; }
        };

        $result = $ref->invoke($controller, $fakeFile, 'docx', $hex, false);

        @unlink($tmp);

        $this->assertTrue($result, 'Expected validateDocxIfNeeded to return true when ZipArchive check is false');
    }
}
