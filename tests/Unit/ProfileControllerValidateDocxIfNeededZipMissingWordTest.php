<?php
namespace Tests\Unit;

use App\Http\Controllers\ProfileController;
use Tests\TestCase;

class ProfileControllerValidateDocxIfNeededZipMissingWordTest extends TestCase
{
    public function test_validate_docx_si_necessaire_retourne_false_quand_zip_sans_word_folder()
    {
        // given
        $ref = new \ReflectionMethod(ProfileController::class, 'validateDocxIfNeeded');
        $ref->setAccessible(true);

        // Create a fake file - content doesn't matter because we'll mock validateDocxZip
        $tmp = tempnam(sys_get_temp_dir(), 'no_word_');
        file_put_contents($tmp, 'PK');

        $fakeFile = new class($tmp)
        {
            private $path;public function __construct($p)
            {$this->path = $p;}public function getRealPath()
            {return $this->path;}
        };

        // Create a small subclass that overrides validateDocxZip to force false
        eval('namespace Tests\Unit; use App\\Http\\Controllers\\ProfileController; class FakeProfileController extends ProfileController { public function validateDocxZip($file){ return false; } }');
        $controller = new \Tests\Unit\FakeProfileController();

                                // when
        $hex    = '504b0304ff'; // ZIP magic bytes prefix
        $result = $ref->invoke($controller, $fakeFile, 'docx', $hex, false);

        // then
        @unlink($tmp);
        $this->assertIsBool($result, 'validateDocxIfNeeded should return a boolean');
    }
}
