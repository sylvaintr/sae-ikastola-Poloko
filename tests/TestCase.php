<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
	use CreatesApplication;

	protected function setUp(): void
	{
		parent::setUp();

        if (method_exists($this, 'withoutVite')) {
            $this->withoutVite();
        }

		// Disable CSRF verification in tests to avoid 419 responses
		$this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

		try {
			$token = bin2hex(random_bytes(16));
		} catch (\Exception $e) {
			$token = 'testingtoken';
		}

		$this->withSession(['_token' => $token]);
		$this->withHeaders([
			'X-CSRF-TOKEN' => $token,
			'X-XSRF-TOKEN' => $token,
		]);
	}

}
