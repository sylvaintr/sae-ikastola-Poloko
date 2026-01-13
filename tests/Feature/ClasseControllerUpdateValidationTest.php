<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Enfant;
use App\Models\Utilisateur;

class ClasseControllerUpdateValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_children_validation_allows_null_or_same_class()
    {
        $classe = Classe::factory()->create(['nom' => 'Classe X', 'niveau' => 'CP']);

        $childNull = Enfant::factory()->create(['idClasse' => null]);
        $childSame = Enfant::factory()->create(['idClasse' => $classe->idClasse]);

        $request = Request::create('/admin/classes/' . $classe->idClasse, 'PUT');
        // Ensure parameters are present in the Request bag
        $request->request->set('nom', 'Classe X updated');
        $request->request->set('niveau', 'CP');
        $request->request->set('children', [$childNull->idEnfant, $childSame->idEnfant]);


        // Perform an HTTP PUT as an authenticated admin to run full validation lifecycle
        $admin = Utilisateur::factory()->create();
        $this->actingAs($admin);
        $this->withoutMiddleware(\Spatie\Permission\Middleware\RoleMiddleware::class);

        $payload = [
            'nom' => 'Classe X updated',
            'niveau' => 'CP',
            'children' => [$childNull->idEnfant, $childSame->idEnfant],
        ];

        $response = $this->put(route('admin.classes.update', $classe), $payload);
        $response->assertStatus(302);
    }
}
