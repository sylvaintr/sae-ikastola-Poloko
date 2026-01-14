<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\ProfileController;

class ProfileControllerReadFileHexShortBytesTest extends TestCase
{
    public function test_lecture_hex_fichier_retourne_null_si_octets_trop_courts()
    {
        // given
        $controller = new ProfileController();
        $ref = new \ReflectionMethod(ProfileController::class, 'readFileHex');
        $ref->setAccessible(true);
        $tmp = tempnam(sys_get_temp_dir(), 'pfht');
        file_put_contents($tmp, 'abc'); // 3 bytes, less than 4
        $fakeFile = new class($tmp) {
            private $path;
            public function __construct($p) { $this->path = $p; }
            public function getRealPath() { return $this->path; }
        };

        // when
        $result = $ref->invoke($controller, $fakeFile);

        // then
        @unlink($tmp);
        $this->assertNull($result);
    }
}
