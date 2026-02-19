<?php
namespace Tests\Unit\Models;

use App\Models\Tache;
use App\Models\TacheHistorique;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TacheHistoriqueTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Teste que les attributs fillable sont correctement assignés.
     */
    public function test_fillable_attributes()
    {
        $data = [
            'idTache'     => 10,
            'statut'      => 'en cours',
            'titre'       => 'Mise à jour du serveur',
            'urgence'     => 'haute',
            'description' => 'Installation des patchs de sécurité',
            'modifie_par' => 5,
        ];

        $historique = new TacheHistorique($data);

        $this->assertEquals(10, $historique->idTache);
        $this->assertEquals('en cours', $historique->statut);
        $this->assertEquals('Mise à jour du serveur', $historique->titre);
        $this->assertEquals('haute', $historique->urgence);
        $this->assertEquals('Installation des patchs de sécurité', $historique->description);
        $this->assertEquals(5, $historique->modifie_par);
    }

    /**
     * Teste la relation belongsTo avec Tache.
     */
    public function test_tache_relation()
    {
        // Création d'une tâche fictive en base
        $tache = Tache::factory()->create();

        // Création d'un historique lié à cette tâche
        $historique = TacheHistorique::factory()->create([
            'idTache' => $tache->idTache,
        ]);

        // Vérifie que la relation retourne bien une instance du modèle Tache
        $this->assertInstanceOf(Tache::class, $historique->tache);
        // Vérifie que c'est bien la bonne tâche
        $this->assertEquals($tache->idTache, $historique->tache->idTache);
    }

    /**
     * Teste la relation belongsTo avec Utilisateur.
     */
    public function test_utilisateur_relation()
    {
        // Création d'un utilisateur fictif en base
        $utilisateur = Utilisateur::factory()->create();

        // Création d'un historique lié à cet utilisateur
        $historique = TacheHistorique::factory()->create([
            'modifie_par' => $utilisateur->idUtilisateur,
        ]);

        // Vérifie que la relation retourne bien une instance du modèle Utilisateur
        $this->assertInstanceOf(Utilisateur::class, $historique->utilisateur);
        // Vérifie que c'est bien le bon utilisateur
        $this->assertEquals($utilisateur->idUtilisateur, $historique->utilisateur->idUtilisateur);
    }

    /**
     * Teste l'accesseur getResponsableAttribute quand l'utilisateur existe.
     */
    public function test_responsable_attribute_with_user()
    {
        // On s'assure que l'utilisateur a l'attribut "nom" attendu par l'accesseur
        $utilisateur = Utilisateur::factory()->create([
            'nom' => 'Jean Dupont',
        ]);

        $historique = TacheHistorique::factory()->create([
            'modifie_par' => $utilisateur->idUtilisateur,
        ]);

        // Recharge le modèle pour s'assurer que les relations sont propres
        $historique = $historique->fresh();

        $this->assertEquals('Jean Dupont', $historique->responsable);
    }

    /**
     * Teste l'accesseur getResponsableAttribute quand il n'y a pas d'utilisateur (null ou supprimé).
     */
    public function test_responsable_attribute_without_user()
    {
        $historique = TacheHistorique::factory()->create([
            'modifie_par' => null, // Aucun utilisateur associé
        ]);

        $this->assertEquals('—', $historique->responsable);
    }

    /**
     * Teste que les attributs de date sont bien castés en objets Carbon.
     */
    public function test_date_casts()
    {
        $historique = TacheHistorique::factory()->create();

        // Vérifie que created_at et updated_at sont bien des instances de Carbon (datetime)
        $this->assertInstanceOf(Carbon::class, $historique->created_at);
        $this->assertInstanceOf(Carbon::class, $historique->updated_at);
    }
}
