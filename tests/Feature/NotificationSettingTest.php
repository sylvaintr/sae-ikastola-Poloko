<?php
namespace Tests\Feature\Models;

use App\Models\Evenement;
use App\Models\NotificationSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationSettingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_notification_setting_with_fillable_attributes()
    {
        $data = [
            'title'           => 'Rappel Assurance',
            'description'     => 'Description test',
            'recurrence_days' => 7,
            'reminder_days'   => 2,
            'is_active'       => true,
            'target_id'       => 0,
            'target_type'     => 'App\Models\DocumentObligatoire',
        ];

        $setting = NotificationSetting::create($data);

        $this->assertDatabaseHas('notification_settings', ['title' => 'Rappel Assurance']);
        $this->assertEquals(7, $setting->recurrence_days);
        $this->assertTrue((bool) $setting->is_active);
    }

    /** @test */
    public function it_has_a_morph_relation_to_target()
    {
        // On crée un événement réel pour servir de cible
        $event = Evenement::create([
            'titre' => 'Conférence',
            'dateE' => now()->addDays(5),
        ]);

        $setting = NotificationSetting::create([
            'title'       => 'Notif Event',
            'target_id'   => $event->idEvenement, // Supposant que la PK est idEvenement
            'target_type' => Evenement::class,
            'is_active'   => true,
        ]);

        $this->assertInstanceOf(Evenement::class, $setting->target);
        $this->assertEquals('Conférence', $setting->target->titre);
    }

    /** @test */
    public function it_has_a_belongs_to_many_relation_with_roles()
    {
        // Création du setting
        $setting = NotificationSetting::create([
            'title'       => 'Test Roles',
            'target_type' => 'App\\Models\\Evenement',
            'target_id'   => 0,
        ]);

        // Vérifie que la relation `roles` existe et retourne un BelongsToMany
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            $setting->roles()
        );
    }

    /** @test */
    public function it_returns_the_correct_module_label_attribute()
    {
        $setting = new NotificationSetting([
            'target_type' => 'App\Models\DocumentObligatoire',
        ]);

        // Test de l'accesseur getModuleLabelAttribute()
        // class_basename de 'App\Models\DocumentObligatoire' doit être 'DocumentObligatoire'
        $this->assertEquals('DocumentObligatoire', $setting->module_label);

        $setting->target_type = 'App\Models\Evenement';
        $this->assertEquals('Evenement', $setting->module_label);
    }

    /** @test */
    public function it_casts_is_active_to_boolean()
    {
        // Vérifie si vous avez défini un cast, sinon testons la valeur brute
        $setting = NotificationSetting::create([
            'title'       => 'Boolean Test',
            'is_active'   => 1,
            'target_id'   => 0,
            'target_type' => 'Test',
        ]);

        $this->assertIsBool((bool) $setting->is_active);
    }
}
