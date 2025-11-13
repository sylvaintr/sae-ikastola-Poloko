<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Role;

class RoleTest extends TestCase
{
    public function test_can_instantiate()
    {
        $model = new Role();
        $this->assertInstanceOf(Role::class, $model);
    }

    public function test_has_table_name()
    {
        $this->assertIsString((new Role())->getTable());
    }
}
