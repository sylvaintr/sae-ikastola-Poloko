<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\Request;
use App\Http\Controllers\PresenceController;

class PresenceControllerExtractClassIdsTest extends TestCase
{
    /**
     * Vérifie que la méthode privée extractClassIds prend en compte
     * la présence de `classe_id` en plus de `classe_ids` (ligne ciblée).
     */
    public function test_extractClassIds_combines_classe_ids_and_classe_id()
    {
        $request = Request::create('/presence', 'GET', [
            'classe_ids' => '1,2',
            'classe_id' => '3',
        ]);

        $controller = new PresenceController();

        $ref = new \ReflectionMethod(PresenceController::class, 'extractClassIds');
        $ref->setAccessible(true);

        $result = $ref->invoke($controller, $request);

        // Expected: both values from classe_ids plus the single classe_id, as integers
        $this->assertEquals([1, 2, 3], $result);
    }
}
