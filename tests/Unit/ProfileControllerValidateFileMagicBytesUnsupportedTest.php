<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\ProfileController;

class ProfileControllerValidateFileMagicBytesUnsupportedTest extends TestCase
{
    public function test_validate_magic_bytes_definit_message_non_supporte_quand_pas_de_magic_bytes_pour_extension()
    {
        // given
        $controller = new ProfileController();

        $ref = new \ReflectionMethod(ProfileController::class, 'validateFileMagicBytes');
        $ref->setAccessible(true);

        $tmp = tempnam(sys_get_temp_dir(), 'pfmb');
        // Write 4 bytes so readFileHex returns a hex string
        file_put_contents($tmp, "abcd");

        $fakeFile = new class($tmp) {
            private $p; public function __construct($p){ $this->p = $p; } public function getRealPath(){ return $this->p; }
        };

        // when
        $result = $ref->invoke($controller, $fakeFile, 'unknownext');

        // then
        @unlink($tmp);

        $this->assertIsArray($result);
        $this->assertFalse($result['valid']);
        $this->assertEquals(__('auth.unsupported_file_type'), $result['message']);
    }
}
