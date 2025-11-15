<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Role;
use App\Models\DocumentObligatoire;
use Carbon\Carbon;

class ObligatoryDocumentControllerIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_documents_with_calculated_fields()
    {
        // Ensure no leftover data from other tests
        Role::query()->delete();
        DocumentObligatoire::query()->delete();

        // Create 2 roles
        $r1 = Role::create(['name' => 'r1']);
        $r2 = Role::create(['name' => 'r2']);

        // Document 1: has dateExpiration and all roles
        $d1 = new DocumentObligatoire();
        $d1->idDocumentObligatoire = 1;
        $d1->nom = 'doc1';
        $d1->dateE = true;
        $d1->dateExpiration = '2030-01-01';
        $d1->save();
        $d1->roles()->sync([$r1->idRole, $r2->idRole]);

        // Document 2: has delai and only one role
        $d2 = new DocumentObligatoire();
        $d2->idDocumentObligatoire = 2;
        $d2->nom = 'doc2';
        $d2->dateE = true;
        $d2->delai = 10;
        $d2->save();
        $d2->roles()->sync([$r1->idRole]);

        // Document 3: dateE is false
        $d3 = new DocumentObligatoire();
        $d3->idDocumentObligatoire = 3;
        $d3->nom = 'doc3';
        $d3->dateE = false;
        $d3->save();

        $controller = new \App\Http\Controllers\Admin\ObligatoryDocumentController();
        $view = $controller->index();

        $this->assertInstanceOf(\Illuminate\View\View::class, $view);

        $data = $view->getData();
        $this->assertArrayHasKey('documents', $data);
        $documents = $data['documents'];

        $this->assertCount(3, $documents);

        // Documents are ordered by idDocumentObligatoire
        $doc1 = $documents->first();
        $this->assertEquals(1, $doc1->idDocumentObligatoire);

        // calculatedExpirationDate for d1 should equal its dateExpiration
        $ced1 = $doc1->calculatedExpirationDate;
        if (is_object($ced1) && method_exists($ced1, 'format')) {
            $ced1 = $ced1->format('Y-m-d');
        }
        $this->assertEquals('2030-01-01', (string) $ced1);

        // d1 has all roles
        $this->assertTrue($doc1->hasAllRoles);

        // Document 2: calculatedExpirationDate should be now + delai
        $doc2 = $documents[1];
        $ced2 = $doc2->calculatedExpirationDate;
        if (is_object($ced2) && method_exists($ced2, 'format')) {
            $ced2 = $ced2->format('Y-m-d');
        }
        $expected = Carbon::now()->addDays(10)->format('Y-m-d');
        $this->assertEquals($expected, (string) $ced2);
        $this->assertFalse($doc2->hasAllRoles);

        // Document3: dateE false -> calculatedExpirationDate null
        $doc3 = $documents[2];
        $this->assertNull($doc3->calculatedExpirationDate);
        $this->assertFalse($doc3->hasAllRoles);
    }
}
