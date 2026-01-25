<?php

namespace Tests\Unit;

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisteredUserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_retourne_vue()
    {
        // given
        $ctrl = new RegisteredUserController();

        // when
        $view = $ctrl->create();

        // then
        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
    }

    public function test_store_cree_utilisateur_quand_valide_et_recaptcha_desactive()
    {
        // given
        config(['services.recaptcha.enabled' => false]);
        $data = [
            'name' => 'Jean Dupont',
            'email' => 'jean@example.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'languePref' => 'fr',
        ];
        $request = Request::create('/register', 'POST', $data);

        // when
        $ctrl = new RegisteredUserController();
        $resp = $ctrl->store($request);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
        $this->assertDatabaseHas((new Utilisateur())->getTable(), ['email' => 'jean@example.test']);
    }

    public function test_verifie_recaptcha_sans_cle_et_environnement_local_accepte()
    {
        // given
        $ctrl = new RegisteredUserController();
        $rm = new \ReflectionMethod(RegisteredUserController::class, 'verifyRecaptcha');
        $rm->setAccessible(true);

        // when
        // case 1: no secret
        config(['services.recaptcha.secret_key' => null]);
        $result1 = $rm->invoke($ctrl, 'any');

        // case 2: local env with test secret
        config(['app.env' => 'local', 'services.recaptcha.secret_key' => 'TEST', 'services.recaptcha.test_secret_key' => 'TEST']);
        $result2 = $rm->invoke($ctrl, 'nonempty');

        // then
        $this->assertFalse($result1);
        $this->assertTrue($result2);
    }

    public function test_store_echoue_si_recaptcha_active_et_manquant()
    {
        // given
        // recaptcha enabled but no response
        config(['services.recaptcha.enabled' => true, 'services.recaptcha.secret_key' => null]);
        $data = [
            'name' => 'Jean Dupont',
            'email' => 'jean2@example.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'g-recaptcha-response' => '',
        ];
        $request = Request::create('/register', 'POST', $data);

        // when
        $ctrl = new RegisteredUserController();
        $resp = $ctrl->store($request);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
        $this->assertDatabaseMissing((new Utilisateur())->getTable(), ['email' => 'jean2@example.test']);
    }

    public function test_store_cree_utilisateur_avec_cles_recaptcha_de_test()
    {
        // given
        // recaptcha enabled and test keys present in local env
        config([
            'services.recaptcha.enabled' => true,
            'services.recaptcha.secret_key' => 'TEST',
            'services.recaptcha.test_secret_key' => 'TEST',
            'app.env' => 'local',
        ]);

        $data = [
            'name' => 'Jean Trois',
            'email' => 'jean3@example.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'g-recaptcha-response' => 'nonempty',
        ];
        $request = Request::create('/register', 'POST', $data);

        // when
        $ctrl = new RegisteredUserController();
        $resp = $ctrl->store($request);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
        $this->assertDatabaseHas((new Utilisateur())->getTable(), ['email' => 'jean3@example.test']);
    }

    public function test_store_attribue_role_parent_si_present()
    {
        // given
        \App\Models\Role::create(['name' => 'parent']);
        config(['services.recaptcha.enabled' => false]);

        $data = [
            'name' => 'Parent Role',
            'email' => 'parentrole@example.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ];
        $request = Request::create('/register', 'POST', $data);

        // when
        $ctrl = new RegisteredUserController();
        $resp = $ctrl->store($request);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
        $user = Utilisateur::where('email', 'parentrole@example.test')->first();
        $this->assertNotNull($user);
        $this->assertGreaterThan(0, $user->rolesCustom()->count());
    }

    public function test_store_cree_utilisateur_avec_prenom_et_nom()
    {
        // given
        config(['services.recaptcha.enabled' => false]);
        $data = [
            'prenom' => 'Pierre',
            'nom' => 'Martin',
            'email' => 'pierre@example.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ];
        $request = Request::create('/register', 'POST', $data);

        // when
        $ctrl = new RegisteredUserController();
        $resp = $ctrl->store($request);

        // then
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
        $this->assertDatabaseHas((new Utilisateur())->getTable(), ['email' => 'pierre@example.test']);
    }

    public function test_verifie_recaptcha_remote_echoue_retourne_false()
    {
        // given
        // remote recaptcha secret (non-test) and env not local
        config(['services.recaptcha.secret_key' => 'REALKEY', 'app.env' => 'production']);

        $ctrl = new RegisteredUserController();
        $rm = new \ReflectionMethod(RegisteredUserController::class, 'verifyRecaptcha');
        $rm->setAccessible(true);

        // when
        $result = $rm->invoke($ctrl, 'some-response');

        // then
        $this->assertFalse($result);
    }
}
