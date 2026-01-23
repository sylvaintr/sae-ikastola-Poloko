<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Classe;
use App\Models\Enfant;
use App\Models\Utilisateur;

class ClasseControllerUpdateChildrenRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_mise_a_jour_echoue_si_enfant_appartient_autre_classe()
    {
        // given
        // classes
        $target = Classe::factory()->create(['nom' => 'Target', 'niveau' => 'CP']);
        $other = Classe::factory()->create(['nom' => 'Other', 'niveau' => 'CP']);

        // enfant déjà rattaché à une autre classe (should be rejected)
        $childOther = Enfant::factory()->create(['idClasse' => $other->idClasse]);

        // authenticate as admin and disable role middleware
        $admin = Utilisateur::factory()->create();
        $this->actingAs($admin);
        $this->withoutMiddleware(\Spatie\Permission\Middleware\RoleMiddleware::class);

        $payload = [
            'nom' => 'New name',
            'niveau' => 'CP',
            'children' => [$childOther->idEnfant],
        ];

        // when
        $response = $this->from(route('admin.classes.edit', $target))
            ->put(route('admin.classes.update', $target), $payload);

        // then
        $response->assertStatus(302);
        $response->assertSessionHasErrors('children.0');
    }
}
