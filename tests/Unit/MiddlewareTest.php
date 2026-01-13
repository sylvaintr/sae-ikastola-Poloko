<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Middleware\DevCspAllowRecaptcha;
use App\Http\Middleware\ProdCspAllowRecaptcha;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MiddlewareTest extends TestCase
{
    public function test_dev_csp_adds_header_in_local_env()
    {
        config(['app.env' => 'local']);

        $middleware = new DevCspAllowRecaptcha();

        $response = $middleware->handle(new Request(), function ($req) {
            return new Response('ok');
        });

        $this->assertTrue($response->headers->has('Content-Security-Policy'));
        $this->assertStringContainsString("google.com", $response->headers->get('Content-Security-Policy'));
    }

    public function test_prod_csp_adds_header_outside_local_env()
    {
        config(['app.env' => 'production']);

        $middleware = new ProdCspAllowRecaptcha();

        $response = $middleware->handle(new Request(), function ($req) {
            return new Response('ok');
        });

        $this->assertTrue($response->headers->has('Content-Security-Policy'));
        $this->assertStringContainsString("www.google.com", $response->headers->get('Content-Security-Policy'));
    }
}
