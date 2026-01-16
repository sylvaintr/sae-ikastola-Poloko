<?php

namespace Tests\Feature;

use App\Http\Controllers\PresenceController;
use App\Models\Classe;
use App\Models\Enfant;
use App\Models\PRATIQUE;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class PresenceControllerSaveSuccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_inserts_presence_rows(): void
    {
        // given
        $classe = Classe::factory()->create();
        $enfants = collect();
        for ($i = 0; $i < 3; $i++) {
            $enfants->push(Enfant::factory()->create([
                'idEnfant' => random_int(100000 + $i, 999999 + $i),
                'idClasse' => $classe->idClasse,
            ]));
        }

        $items = [];
        foreach ($enfants as $i => $enfant) {
            $items[] = [
                'idEnfant' => $enfant->idEnfant,
                'present' => $i % 2 === 0, // alternate
            ];
        }

        $controller = new PresenceController();

        // when
        $request = Request::create('/presence/save', 'POST', [
            'date' => now()->format('Y-m-d'),
            'activite' => 'cantine',
            'items' => $items,
        ]);

        $response = $controller->save($request);

        // then
        $this->assertEquals(200, $response->getStatusCode());

        // Check DB for present entries
        $ids = $enfants->pluck('idEnfant')->all();
        $presentCount = PRATIQUE::whereIn('idEnfant', $ids)
            ->where('activite', 'cantine')
            ->whereDate('dateP', now()->format('Y-m-d'))
            ->count();

        $expected = collect($items)->filter(fn($i) => $i['present'])->count();
        $this->assertEquals($expected, $presentCount);
    }
}
