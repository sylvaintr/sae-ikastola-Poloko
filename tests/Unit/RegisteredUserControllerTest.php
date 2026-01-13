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

    public function test_create_returns_view()
    {
        // given
        $ctrl = new RegisteredUserController();

        // when
        $view = $ctrl->create();

        // then
        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
    }

    public function test_store_creates_user_when_valid_and_recaptcha_disabled()
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

    public function test_verify_recaptcha_behaviors()
    {
        // given
        $ctrl = new RegisteredUserController();
        $rm = new \ReflectionMethod(RegisteredUserController::class, 'verifyRecaptcha');
        $rm->setAccessible(true);

        // when / then: no secret -> false
        config(['services.recaptcha.secret_key' => null]);
        $this->assertFalse($rm->invoke($ctrl, 'any'));

        // when / then: local env with test secret accepts non-empty
        config(['app.env' => 'local', 'services.recaptcha.secret_key' => 'TEST', 'services.recaptcha.test_secret_key' => 'TEST']);
        $this->assertTrue($rm->invoke($ctrl, 'nonempty'));
    }

    public function test_store_fails_when_recaptcha_enabled_and_missing()
    {
        // given: recaptcha enabled but no response
        config(['services.recaptcha.enabled' => true, 'services.recaptcha.secret_key' => null]);
        $data = [
            'name' => 'Jean Dupont',
            'email' => 'jean2@example.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'g-recaptcha-response' => '',
        ];
        $request = Request::create('/register', 'POST', $data);

        $ctrl = new RegisteredUserController();
        $resp = $ctrl->store($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
        $this->assertDatabaseMissing((new Utilisateur())->getTable(), ['email' => 'jean2@example.test']);
    }

    public function test_store_with_recaptcha_test_keys_creates_user()
    {
        // given: recaptcha enabled and test keys present in local env
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

        $ctrl = new RegisteredUserController();
        $resp = $ctrl->store($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
        $this->assertDatabaseHas((new Utilisateur())->getTable(), ['email' => 'jean3@example.test']);
    }

    public function test_store_assigns_parent_role_when_present()
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

        $ctrl = new RegisteredUserController();
        $resp = $ctrl->store($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
        $user = Utilisateur::where('email', 'parentrole@example.test')->first();
        $this->assertNotNull($user);
        $this->assertGreaterThan(0, $user->rolesCustom()->count());
    }

    public function test_store_with_prenom_nom_creates_user()
    {
        config(['services.recaptcha.enabled' => false]);
        $data = [
            'prenom' => 'Pierre',
            'nom' => 'Martin',
            'email' => 'pierre@example.test',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ];
        $request = Request::create('/register', 'POST', $data);

        $ctrl = new RegisteredUserController();
        $resp = $ctrl->store($request);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $resp);
        $this->assertDatabaseHas((new Utilisateur())->getTable(), ['email' => 'pierre@example.test']);
    }

    public function test_verify_recaptcha_remote_failure_returns_false()
    {
        // given: remote recaptcha secret (non-test) and env not local
        config(['services.recaptcha.secret_key' => 'REALKEY', 'app.env' => 'production']);

        $ctrl = new RegisteredUserController();
        $rm = new \ReflectionMethod(RegisteredUserController::class, 'verifyRecaptcha');
        $rm->setAccessible(true);

        // when: call with a response; network call will likely fail in test env
        $this->assertFalse($rm->invoke($ctrl, 'some-response'));
    }
}
