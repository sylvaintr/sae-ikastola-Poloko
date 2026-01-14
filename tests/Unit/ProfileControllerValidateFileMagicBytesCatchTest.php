<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\ProfileController;

class ProfileControllerValidateFileMagicBytesCatchTest extends TestCase
{
    public function test_validate_magic_bytes_attrape_exceptions_et_retourne_message_erreur()
    {
        // given
        // none

        // when

        // then
        $controller = new ProfileController();

        $ref = new \ReflectionMethod(ProfileController::class, 'validateFileMagicBytes');
        $ref->setAccessible(true);

        $fakeFile = new class {
            public function getRealPath()
            {
                throw new \Exception('boom');
            }
        };

        $result = $ref->invoke($controller, $fakeFile, 'pdf');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertFalse($result['valid']);
        $this->assertEquals(__('auth.file_validation_error'), $result['message']);
    }
}
