<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Utilisateur;

class UtilisateurPasswordMutatorTest extends TestCase
{
    public function test_setPasswordAttribute_hashes_plain_password()
    {
        // given
        $user = new Utilisateur();
        $plain = 'secret-Password-123';

        // when
        $result = $user->setPasswordAttribute($plain);

        // then
        $this->assertIsString($result);
        $this->assertTrue(password_verify($plain, $result));
    }

    public function test_hachage_mot_de_passe_quand_clair()
    {
        // given
        $user = new Utilisateur();
        $plain = 'anotherSecret!';

        // when
        $result = $user->setPasswordAttribute(password_hash($plain, PASSWORD_DEFAULT));

        // then
        $this->assertIsString($result);
    }

    public function test_retourne_identique_si_deja_hache()
    {
        // given
        $user = new Utilisateur();
        $plain = 'anotherSecret!';
        $hashed = password_hash($plain, PASSWORD_DEFAULT);

        // when
        $result = $user->setPasswordAttribute($hashed);

        // then
        $this->assertSame($hashed, $result);
    }
}
