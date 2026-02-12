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
        // given
        $classe = Classe::factory()->create(['nom' => 'Classe X', 'niveau' => 'CP']);
        $childNull = Enfant::factory()->create(['idClasse' => null]);
        $childSame = Enfant::factory()->create(['idClasse' => $classe->idClasse]);

        // Ensure parameters are present in the Request bag
        $request = Request::create('/admin/classes/' . $classe->idClasse, 'PUT');
        $request->request->set('nom', 'Classe X updated');
        $request->request->set('niveau', 'CP');
        $request->request->set('children', [$childNull->idEnfant, $childSame->idEnfant]);

        // authenticate as admin and disable role middleware
        $admin = Utilisateur::factory()->create();
        $admin->assignRole('CA');
        $this->actingAs($admin);
        $this->withoutMiddleware(\Spatie\Permission\Middleware\RoleMiddleware::class);

        $payload = [
            'nom' => 'Classe X updated',
            'niveau' => 'CP',
            'children' => [$childNull->idEnfant, $childSame->idEnfant],
        ];

        // when
        $response = $this->put(route('admin.classes.update', $classe), $payload);

        // then
        $response->assertStatus(302);
    }
}
