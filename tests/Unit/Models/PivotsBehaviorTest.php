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
        $famille = Famille::factory()->create();
        $user = Utilisateur::factory()->create();

        $famille->utilisateurs()->attach($user->idUtilisateur, ['parite' => 'parent']);

        $this->assertDatabaseHas('lier', ['idFamille' => $famille->idFamille, 'idUtilisateur' => $user->idUtilisateur, 'parite' => 'parent']);

        // Ensure we inspect the pivot for the specific user we attached (factory may have created other liens)
        $fetched = $famille->utilisateurs()->where('utilisateur.idUtilisateur', $user->idUtilisateur)->first();
        $this->assertNotNull($fetched);
        $this->assertEquals('parent', $fetched->pivot->parite);
    }

    public function test_realiser_pivot_created_and_relations_work()
    {
        $user = Utilisateur::factory()->create();
        $tache = Tache::factory()->create();

        $user->tachesRealisees()->attach($tache->idTache, ['dateM' => now(), 'description' => 'done']);

        $this->assertDatabaseHas('realiser', ['idUtilisateur' => $user->idUtilisateur, 'idTache' => $tache->idTache]);

        $fetched = $user->tachesRealisees()->first();
        $this->assertNotNull($fetched->pivot->description);
    }

    public function test_joindre_and_contenir_and_inclure_and_correspondre_and_attribuer()
    {
        // Joindre: document <-> actualite
        $doc = Document::factory()->create();
        $act = Actualite::factory()->create();
        $act->documents()->attach($doc->idDocument);
        $this->assertDatabaseHas('joindre', ['idDocument' => $doc->idDocument, 'idActualite' => $act->idActualite]);

        // Contenir: utilisateur <-> document
        $user = Utilisateur::factory()->create();
        $doc2 = Document::factory()->create();
        $user->documents()->attach($doc2->idDocument);
        $this->assertDatabaseHas('contenir', ['idUtilisateur' => $user->idUtilisateur, 'idDocument' => $doc2->idDocument]);

        // Inclure: evenement <-> materiel
        $mat = Materiel::factory()->create();
        $evt = Evenement::factory()->create();
        $evt->materiels()->attach($mat->idMateriel ?? $mat->id);
        // Table name is 'inclure'
        $this->assertDatabaseHas('inclure', ['idEvenement' => $evt->idEvenement ?? $evt->getKey(), 'idMateriel' => $mat->idMateriel ?? $mat->getKey()]);

        // Correspondre: actualite <-> etiquette
        $et = Etiquette::factory()->create();
        $act2 = Actualite::factory()->create();
        $act2->etiquettes()->attach($et->idEtiquette ?? $et->getKey());
        $this->assertDatabaseHas('correspondre', ['idActualite' => $act2->idActualite ?? $act2->getKey(), 'idEtiquette' => $et->idEtiquette ?? $et->getKey()]);

        // Attribuer: role <-> documentObligatoire
        $role = Role::factory()->create();
        $docOb = DocumentObligatoire::factory()->create();
        $role->documentObligatoires()->attach($docOb->idDocumentObligatoire ?? $docOb->getKey());
        $this->assertDatabaseHas('attribuer', ['idRole' => $role->idRole ?? $role->getKey(), 'idDocumentObligatoire' => $docOb->idDocumentObligatoire ?? $docOb->getKey()]);
    }

    public function test_classe_has_enfants_relation()
    {
        $classe = Classe::factory()->create();
        Enfant::factory()->create(['idClasse' => $classe->idClasse]);

        $this->assertGreaterThanOrEqual(1, $classe->enfants()->count());
    }
}
