<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Role;

class RoleModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_factory_creates_record()
    {
        $role = Role::factory()->create();

        $this->assertDatabaseHas('role', ['name' => $role->name]);
        $this->assertGreaterThanOrEqual(0,  $role->documentObligatoires()->count());
    }
}
