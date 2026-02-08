<?php
namespace Tests\Unit;

use App\Http\Controllers\ActualiteController;
use App\Http\Controllers\ClasseController;
use App\Http\Controllers\EtiquetteController;
use App\Http\Controllers\PresenceController;
use App\Models\Classe;
use App\Models\Enfant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ControllersHelpersTests extends TestCase
{
    use RefreshDatabase;
    public function test_ensureEtiquetteIsPublicColumn_called_on_controllers()
    {
        Schema::shouldReceive('hasColumn')->times(2)->with('etiquette', 'public')->andReturn(false);
        Schema::shouldReceive('table')->times(2)->with('etiquette', \Mockery::on(function ($closure) {$bp = new class {public function boolean($n)
                {return $this;}public function default($v)
                {return $this;}public function after($c)
                {return $this;}};; $closure($bp);return true;}));

        $act    = new ActualiteController();
        $refl   = new \ReflectionClass($act);
        $method = $refl->getMethod('ensureEtiquetteIsPublicColumn');
        $method->setAccessible(true);
        $method->invoke($act);

        $et    = new EtiquetteController();
        $refl2 = new \ReflectionClass($et);
        $m2    = $refl2->getMethod('ensureEtiquetteIsPublicColumn');
        $m2->setAccessible(true);
        $m2->invoke($et);

        // assertions are implicit via Mockery expectations
        $this->addToAssertionCount(1);
    }

    public function test_extractClassIds_handles_nested_array()
    {
        $ctr    = new PresenceController();
        $refl   = new \ReflectionClass($ctr);
        $method = $refl->getMethod('extractClassIds');
        $method->setAccessible(true);

        $request = Request::create('/?');
        $request->query->set('classe_ids', [['1', '2']]);

        $res = $method->invoke($ctr, $request);
        $this->assertSame([1, 2], $res);
    }

    public function test_update_validation_rule_exists_executes_where_closure()
    {
        \Illuminate\Support\Facades\Event::fake();

        $classe = Classe::factory()->create();
        $enfant = Enfant::factory()->create(['idClasse' => null]);

        $request = Request::create('/', 'POST', ['nom' => 'C', 'niveau' => 'X', 'children' => [$enfant->idEnfant]]);

        $ctrl = new ClasseController();
        $res  = $ctrl->update($request, $classe);

        $this->assertNotNull($res);
    }
}
