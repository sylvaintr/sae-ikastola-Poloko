<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\DocumentObligatoire;
use Illuminate\Support\Facades\DB;

class ObligatoryDocumentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_find_available_id_on_empty_table_returns_one()
    {
        $controller = new \App\Http\Controllers\Admin\ObligatoryDocumentController();
        $ref = new \ReflectionMethod($controller, 'findAvailableId');
        $ref->setAccessible(true);

        $id = $ref->invoke($controller);
        $this->assertEquals(1, $id);
    }

    public function test_find_available_id_finds_gap_and_returns_smallest_missing()
    {
        // ensure table is empty to avoid clashes from other tests
        \App\Models\DocumentObligatoire::query()->delete();

        // create records with ids 1,2,4
        $d1 = new DocumentObligatoire();
        $d1->idDocumentObligatoire = 1;
        $d1->save();

        $d2 = new DocumentObligatoire();
        $d2->idDocumentObligatoire = 2;
        $d2->save();

        $d4 = new DocumentObligatoire();
        $d4->idDocumentObligatoire = 4;
        $d4->save();

        $controller = new \App\Http\Controllers\Admin\ObligatoryDocumentController();
        $ref = new \ReflectionMethod($controller, 'findAvailableId');
        $ref->setAccessible(true);

        $id = $ref->invoke($controller);
        $this->assertEquals(3, $id);
    }

    public function test_find_available_id_returns_max_plus_one_when_no_gaps()
    {
        // ensure table is empty to avoid clashes from other tests
        \App\Models\DocumentObligatoire::query()->delete();

        // create records 1..3
        for ($i = 1; $i <= 3; $i++) {
            $d = new DocumentObligatoire();
            $d->idDocumentObligatoire = $i;
            $d->save();
        }

        $controller = new \App\Http\Controllers\Admin\ObligatoryDocumentController();
        $ref = new \ReflectionMethod($controller, 'findAvailableId');
        $ref->setAccessible(true);

        $id = $ref->invoke($controller);
        $this->assertEquals(4, $id);
    }

    /**
     * @runInSeparateProcess
     */
    public function test_get_nom_max_length_returns_integer_and_is_cached()
    {
        $controller = new \App\Http\Controllers\Admin\ObligatoryDocumentController();
        $ref = new \ReflectionMethod($controller, 'getNomMaxLength');
        $ref->setAccessible(true);

        $len1 = $ref->invoke($controller);
        $len2 = $ref->invoke($controller);

        $this->assertIsInt($len1);
        $this->assertEquals($len1, $len2, 'Value should be cached and identical on subsequent calls');
        $this->assertGreaterThanOrEqual(1, $len1);
        $this->assertLessThanOrEqual(1000, $len1);
    }

    /**
     * @runInSeparateProcess
     */
    public function test_get_nom_max_length_returns_default_on_exception()
    {
        // Force DB to throw
        DB::shouldReceive('table')->andThrow(new \Exception('boom'));

        $controller = new \App\Http\Controllers\Admin\ObligatoryDocumentController();
        $ref = new \ReflectionMethod($controller, 'getNomMaxLength');
        $ref->setAccessible(true);

        $len = $ref->invoke($controller);
        $this->assertEquals(100, $len);
    }
}
