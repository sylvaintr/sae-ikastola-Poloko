<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Famille;
use App\Models\Utilisateur;
use App\Models\Tache;
use App\Models\Document;
use App\Models\Actualite;
use App\Models\Evenement;
use App\Models\Materiel;
use App\Models\Etiquette;
use App\Models\DocumentObligatoire;
use App\Models\Role;
use App\Models\Classe;
use App\Models\Enfant;

class PivotsBehaviorTest extends TestCase
{
    use RefreshDatabase;

    public function test_lier_pivot_created_and_relations_work()
    {
        // given
        $famille = Famille::factory()->create();
        $user = Utilisateur::factory()->create();

        // when
        $famille->utilisateurs()->attach($user->idUtilisateur, ['parite' => 'parent']);

        // then
        $this->assertDatabaseHas('lier', ['idFamille' => $famille->idFamille, 'idUtilisateur' => $user->idUtilisateur, 'parite' => 'parent']);

        // Ensure we inspect the pivot for the specific user we attached (factory may have created other liens)
        $fetched = $famille->utilisateurs()->where('utilisateur.idUtilisateur', $user->idUtilisateur)->first();
        $this->assertNotNull($fetched);
        $this->assertEquals('parent', $fetched->pivot->parite);
    }

    public function test_realiser_pivot_created_and_relations_work()
    {
        // given
        $user = Utilisateur::factory()->create();
        $tache = Tache::factory()->create();

        // when
        $user->tachesRealisees()->attach($tache->idTache, ['dateM' => now(), 'description' => 'done']);

        // then
        $this->assertDatabaseHas('realiser', ['idUtilisateur' => $user->idUtilisateur, 'idTache' => $tache->idTache]);

        $fetched = $user->tachesRealisees()->first();
        $this->assertNotNull($fetched->pivot->description);
    }

    public function test_joindre_and_contenir_and_inclure_and_correspondre_and_attribuer()
    {
        // given
        // Joindre: document <-> actualite
        $doc = Document::factory()->create();
        $act = Actualite::factory()->create();

        // when
        $act->documents()->attach($doc->idDocument);

        // then
        $this->assertDatabaseHas('joindre', ['idDocument' => $doc->idDocument, 'idActualite' => $act->idActualite]);

        // given
        // Contenir: utilisateur <-> document
        $user = Utilisateur::factory()->create();
        $doc2 = Document::factory()->create();

        // when
        $user->documents()->attach($doc2->idDocument);

        // then
        $this->assertDatabaseHas('contenir', ['idUtilisateur' => $user->idUtilisateur, 'idDocument' => $doc2->idDocument]);

        // given
        // Inclure: evenement <-> materiel
        $mat = Materiel::factory()->create();
        $evt = Evenement::factory()->create();

        // when
        $evt->materiels()->attach($mat->idMateriel ?? $mat->id);

        // then
        $this->assertDatabaseHas('inclure', ['idEvenement' => $evt->idEvenement ?? $evt->getKey(), 'idMateriel' => $mat->idMateriel ?? $mat->getKey()]);

        // given
        // Correspondre: actualite <-> etiquette
        $et = Etiquette::factory()->create();
        $act2 = Actualite::factory()->create();

        // when
        $act2->etiquettes()->attach($et->idEtiquette ?? $et->getKey());

        // then
        $this->assertDatabaseHas('correspondre', ['idActualite' => $act2->idActualite ?? $act2->getKey(), 'idEtiquette' => $et->idEtiquette ?? $et->getKey()]);

        // given
        // Attribuer: role <-> documentObligatoire
        $role = Role::factory()->create();
        $docOb = DocumentObligatoire::factory()->create();

        // when
        $role->documentObligatoires()->attach($docOb->idDocumentObligatoire ?? $docOb->getKey());

        // then
        $this->assertDatabaseHas('attribuer', ['idRole' => $role->idRole ?? $role->getKey(), 'idDocumentObligatoire' => $docOb->idDocumentObligatoire ?? $docOb->getKey()]);
    }

    public function test_classe_has_enfants_relation()
    {
        // given
        $classe = Classe::factory()->create();
        Enfant::factory()->create(['idClasse' => $classe->idClasse]);

        // when
        $count = $classe->enfants()->count();

        // then
        $this->assertGreaterThanOrEqual(1, $count);
    }
}
