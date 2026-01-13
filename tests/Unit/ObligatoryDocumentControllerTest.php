<?php

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Admin\ObligatoryDocumentController;
use App\Models\DocumentObligatoire;

class ObligatoryDocumentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset the controller's cached nom length before each test
        $prop = new \ReflectionProperty(\App\Http\Controllers\Admin\ObligatoryDocumentController::class, 'cachedNomMaxLength');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
    }


    public function test_getNomMaxLength_retourne_valeur_db_quand_presente()
    {
        $builder = Mockery::mock();
        $builder->shouldReceive('where')->andReturnSelf();
        $builder->shouldReceive('value')->with('character_maximum_length')->andReturn('150');

        DB::shouldReceive('table')->with('information_schema.columns')->andReturn($builder);
        DB::shouldReceive('getDatabaseName')->andReturn('test_db');

        $controller = new ObligatoryDocumentController();
        $ref = new \ReflectionMethod(ObligatoryDocumentController::class, 'getNomMaxLength');
        $ref->setAccessible(true);

        $result = $ref->invoke($controller);

        $this->assertSame(150, $result);
    }

    public function test_getNomMaxLength_retourne_defaut_quand_null()
    {
        $builder = Mockery::mock();
        $builder->shouldReceive('where')->andReturnSelf();
        $builder->shouldReceive('value')->with('character_maximum_length')->andReturn(null);

        DB::shouldReceive('table')->with('information_schema.columns')->andReturn($builder);
        DB::shouldReceive('getDatabaseName')->andReturn('test_db');

        $controller = new ObligatoryDocumentController();
        $ref = new \ReflectionMethod(ObligatoryDocumentController::class, 'getNomMaxLength');
        $ref->setAccessible(true);

        $result = $ref->invoke($controller);

        $this->assertSame(100, $result);
    }


    public function test_getNomMaxLength_retourne_defaut_sur_exception()
    {
        $builder = Mockery::mock();
        $builder->shouldReceive('where')->andThrow(new \Exception('db error'));

        DB::shouldReceive('table')->with('information_schema.columns')->andReturn($builder);
        DB::shouldReceive('getDatabaseName')->andReturn('test_db');

        $controller = new ObligatoryDocumentController();
        $ref = new \ReflectionMethod(ObligatoryDocumentController::class, 'getNomMaxLength');
        $ref->setAccessible(true);

        $result = $ref->invoke($controller);

        $this->assertSame(100, $result);
    }

    public function test_trouver_id_disponible_table_vide_retourne_un()
    {
        DocumentObligatoire::query()->delete();

        $controller = new ObligatoryDocumentController();
        $ref = new \ReflectionMethod($controller, 'findAvailableId');
        $ref->setAccessible(true);

        $id = $ref->invoke($controller);
        $this->assertEquals(1, $id);
    }

    public function test_trouver_id_disponible_trouve_ecart_et_retourne_plus_petit_manquant()
    {
        DocumentObligatoire::query()->delete();

        $d1 = new DocumentObligatoire();
        $d1->idDocumentObligatoire = 1;
        $d1->save();

        $d2 = new DocumentObligatoire();
        $d2->idDocumentObligatoire = 2;
        $d2->save();

        $d4 = new DocumentObligatoire();
        $d4->idDocumentObligatoire = 4;
        $d4->save();

        $controller = new ObligatoryDocumentController();
        $ref = new \ReflectionMethod($controller, 'findAvailableId');
        $ref->setAccessible(true);

        $id = $ref->invoke($controller);
        $this->assertEquals(3, $id);
    }

    public function test_trouver_id_disponible_retourne_max_plus_un_quand_pas_de_gaps()
    {
        DocumentObligatoire::query()->delete();

        for ($i = 1; $i <= 3; $i++) {
            $d = new DocumentObligatoire();
            $d->idDocumentObligatoire = $i;
            $d->save();
        }

        $controller = new ObligatoryDocumentController();
        $ref = new \ReflectionMethod($controller, 'findAvailableId');
        $ref->setAccessible(true);

        $id = $ref->invoke($controller);
        $this->assertEquals(4, $id);
    }

    public function test_get_nom_max_length_retourne_entier_et_est_mis_en_cache()
    {
        $controller = new ObligatoryDocumentController();
        $ref = new \ReflectionMethod($controller, 'getNomMaxLength');
        $ref->setAccessible(true);

        $len1 = $ref->invoke($controller);
        $len2 = $ref->invoke($controller);

        $this->assertIsInt($len1);
        $this->assertEquals($len1, $len2, 'Value should be cached and identical on subsequent calls');
        $this->assertGreaterThanOrEqual(1, $len1);
        $this->assertLessThanOrEqual(1000, $len1);
    }

    public function test_get_nom_max_length_retourne_defaut_sur_exception()
    {
        DB::shouldReceive('table')->andThrow(new \Exception('boom'));

        $controller = new ObligatoryDocumentController();
        $ref = new \ReflectionMethod($controller, 'getNomMaxLength');
        $ref->setAccessible(true);

        $len = $ref->invoke($controller);
        $this->assertEquals(100, $len);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
