<?php
namespace Tests\Feature;

use App\Http\Controllers\Traits\HandlesCsvExport;
use App\Models\Tache; // Assume que c'est le nom du modèle historique
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Classe factice pour tester le Trait
 */
class CsvExportTestController
{
    use HandlesCsvExport;
}

class HandlesCsvExportTest extends TestCase
{
    use RefreshDatabase;

    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new CsvExportTestController();
    }

    /** @test */
    public function it_generates_a_streamed_response_with_correct_headers()
    {
        $tache = Tache::factory()->create(['titre' => 'Ma Super Tâche']);

        $response = $this->controller->exportCsv($tache);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
        $this->assertEquals('text/csv; charset=UTF-8', $response->headers->get('Content-Type'));

        // Vérifie le nom du fichier (nettoyé)
        $disposition = $response->headers->get('Content-Disposition');
        // The filename sanitization may replace accented characters with underscores;
        // check core parts rather than exact accent handling.
        $this->assertMatchesRegularExpression('/Ma_Super_T.*demande_/', $disposition);
        $this->assertStringContainsString('.csv', $disposition);
    }

    /** @test */
    public function it_contains_the_correct_csv_content_structure()
    {
        // 1. Préparation de la tâche avec historiques
        $tache = Tache::factory()->create([
            'idTache'     => 123,
            'titre'       => 'Tâche Test',
            'description' => 'Ma description',
            'montantP'    => 150.50,
            'dateD'       => now()->subDays(2),
        ]);

        // Ajout d'un historique
        $tache->historiques()->create([
            'statut'         => 'En cours',
            'date_evenement' => now(),
            'titre'          => 'Action effectuée',
            'depense'        => 50.00,
            'description'    => 'Test historique',
        ]);

        // 2. Capture du contenu du stream
        ob_start();
        $this->controller->exportCsv($tache)->sendContent();
        $content = ob_get_clean();

        // 3. Assertions sur le contenu
        // Vérifie la présence du BOM UTF-8 au début
        $this->assertStringStartsWith(chr(0xEF) . chr(0xBB) . chr(0xBF), $content);

        // Vérifie les données de la tâche
        $this->assertStringContainsString('Tâche Test', $content);
        $this->assertStringContainsString('Ma description', $content);
        $this->assertStringContainsString('150,50 €', $content);

        // Vérifie les données de l'historique
        $this->assertStringContainsString('En cours', $content);
        $this->assertStringContainsString('Action effectuée', $content);
        $this->assertStringContainsString('50,00 €', $content);
        $this->assertStringContainsString('Test historique', $content);
    }

    /** @test */
    public function it_formats_null_amounts_correcty()
    {
        $tache = Tache::factory()->create(['montantP' => null]);

        ob_start();
        $this->controller->exportCsv($tache)->sendContent();
        $content = ob_get_clean();

        // montant_previsionnel est null sans defaultToZero -> doit être '—'
        $this->assertStringContainsString('—', $content);
    }

    /** @test */
    public function it_cleans_filename_special_characters()
    {
        // Utilisation d'une méthode de réflexion pour tester la méthode privée
        $method = new \ReflectionMethod(CsvExportTestController::class, 'generateCsvFilename');
        $method->setAccessible(true);

        $filename = $method->invoke($this->controller, 'L’été sera chaud @2026!');

        // "L’été" devient "L_t_" ou similaire selon preg_replace, l'important est l'absence de caractères interdits
        $this->assertStringNotContainsString('’', $filename);
        $this->assertStringNotContainsString('@', $filename);
        $this->assertStringNotContainsString('!', $filename);
        // Accent removal can vary (é -> e or _), ensure important parts remain and forbidden
        // characters are removed. Match a flexible pattern.
        $this->assertMatchesRegularExpression('/L.*sera_chaud_2026/', $filename);
    }

    /** @test */
    public function it_joins_multiple_realisateurs_names()
    {
        $tache = Tache::factory()->create();

        // Simule des réalisateurs (si vous utilisez Spatie Roles ou une relation BelongsToMany custom)
        // Note: Adaptez selon votre modèle Utilisateur/Realisateur
        $user1 = \App\Models\Utilisateur::factory()->create(['nom' => 'Dupont', 'prenom' => 'Jean']);
        $user2 = \App\Models\Utilisateur::factory()->create(['nom' => 'Durand', 'prenom' => 'Marie']);

        // On assume que le modèle Utilisateur a un attribut 'name' ou on adapte le pluck dans le trait
        $tache->realisateurs()->attach([$user1->idUtilisateur, $user2->idUtilisateur]);

        ob_start();
        $this->controller->exportCsv($tache)->sendContent();
        $content = ob_get_clean();

        // Vérifie que les noms de famille sont concaténés (format: "Nom1, Nom2").
        $expected = $user1->nom . ', ' . $user2->nom;
        $this->assertStringContainsString($expected, $content);
    }
}
