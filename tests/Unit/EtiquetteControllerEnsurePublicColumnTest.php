<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\EtiquetteController;

class EtiquetteControllerEnsurePublicColumnTest extends TestCase
{
    use RefreshDatabase;

    public function test_ensure_etiquette_public_column_calls_schema_table_when_missing()
    {
        // Arrange: Schema::hasColumn returns false so the controller should call Schema::table
        Schema::shouldReceive('hasColumn')->once()->with('etiquette', 'is_public')->andReturn(false);

        Schema::shouldReceive('table')
            ->once()
            ->with('etiquette', \Mockery::on(function ($arg) {
                // second parameter should be a Closure that will be invoked by Schema::table
                return is_callable($arg);
            }))
            ->andReturnNull();

        $controller = new EtiquetteController();

        // Use reflection to call private method
        $ref = new \ReflectionClass(EtiquetteController::class);
        $method = $ref->getMethod('ensureEtiquetteIsPublicColumn');
        $method->setAccessible(true);

        // Act
        $method->invoke($controller);

        // Assert: expectations on Schema mocked via shouldReceive are verified by Mockery on test teardown
        $this->assertTrue(true);
    }
}
