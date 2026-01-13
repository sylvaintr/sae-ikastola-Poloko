<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\ProfileController;

class ProfileControllerReadFileHexTest extends TestCase
{
    public function test_lire_octets_hex_retourne_null_si_fopen_echoue()
    {
        $controller = new ProfileController();

        $ref = new \ReflectionMethod(ProfileController::class, 'readFileHex');
        $ref->setAccessible(true);

        $fakeFile = new class {
            public function getRealPath()
            {
                return '/path/that/does/not/exist/for/test_profile_readfilehex';
            }
        };

        // Temporarily suppress PHP warnings so fopen failure does not raise ErrorException
        $prevHandler = set_error_handler(function () { return true; });
        try {
            $result = $ref->invoke($controller, $fakeFile);
        } finally {
            if ($prevHandler !== null) {
                restore_error_handler();
            }
        }

        $this->assertNull($result);
    }
}
