<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\ProfileController;

class ProfileControllerValidateFileMagicBytesNoHexTest extends TestCase
{
    public function test_validate_magic_bytes_retourne_impossible_lire_si_hex_false()
    {
        $controller = new ProfileController();

        $ref = new \ReflectionMethod(ProfileController::class, 'validateFileMagicBytes');
        $ref->setAccessible(true);

        // Ensure namespaced fopen used by readFileHex returns false
        if (!function_exists('App\\Http\\Controllers\\fopen')) {
            eval('namespace App\\Http\\Controllers; function fopen($path, $mode) { return false; }');
        }

        $fakeFile = new class {
            public function getRealPath() { return '/non/existent/path/for/test'; }
        };

        // Suppress PHP warnings so fopen failure inside readFileHex does not raise an ErrorException
        $prevHandler = set_error_handler(function () { return true; });
        try {
            $result = $ref->invoke($controller, $fakeFile, 'pdf');
        } finally {
            if ($prevHandler !== null) {
                restore_error_handler();
            }
        }

        $this->assertIsArray($result);
        $this->assertFalse($result['valid']);
        $this->assertEquals(__('auth.cannot_read_file'), $result['message']);
    }
}
