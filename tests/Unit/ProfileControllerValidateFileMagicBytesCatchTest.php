<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\ProfileController;

class ProfileControllerValidateFileMagicBytesCatchTest extends TestCase
{
    public function test_validateFileMagicBytes_catches_exceptions_and_returns_error_message()
    {
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
