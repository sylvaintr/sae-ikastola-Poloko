<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Mockery;
use Illuminate\Http\RedirectResponse;

class ActualiteControllerTest extends TestCase {

    public function testExample()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
