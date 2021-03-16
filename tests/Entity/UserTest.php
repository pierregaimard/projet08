<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserRole()
    {
        $user = new User();

        # Initial user role should be ROLE_USER
        $this->assertEquals([User::ROLE_USER], $user->getRoles());
        $this->assertEquals('Utilisateur', $user->getRole());

        $user->setRoles([User::ROLE_ADMIN]);

        # Test with ROLE_ADMIN
        $this->assertEquals([User::ROLE_ADMIN], $user->getRoles());
        $this->assertEquals('Administrateur', $user->getRole());
    }

    public function testEraseCredentials()
    {
        $user = new User();

        $user->setPlainPassword('MyFavoritePassword');
        $user->eraseCredentials();

        $this->assertNull($user->getPlainPassword());
    }
}
