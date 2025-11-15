<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Utilisateur;

class UtilisateurPasswordMutatorTest extends TestCase
{
    public function test_setPasswordAttribute_hashes_plain_password()
    {
        $user = new Utilisateur();

        $plain = 'secret-Password-123';
        $result = $user->setPasswordAttribute($plain);

        $this->assertIsString($result);
        $this->assertTrue(password_verify($plain, $result));
    }

    public function test_setPasswordAttribute_returns_same_when_already_hashed()
    {
        $user = new Utilisateur();

        $plain = 'anotherSecret!';
        $hashed = password_hash($plain, PASSWORD_DEFAULT);

        $result = $user->setPasswordAttribute($hashed);

        $this->assertSame($hashed, $result);
    }
}
