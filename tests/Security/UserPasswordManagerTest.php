<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\UserPasswordManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserPasswordManagerTest extends TestCase
{
    public function testSetUserPasswordMethod()
    {
        $mock = $this->getMockBuilder(UserPasswordEncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('encodePassword')->willReturn('MyEncodedPassword');

        $manager = new UserPasswordManager($mock);
        $user = new User();

        $this->assertFalse($manager->setUserPassword($user));
        $user->setPlainPassword('MyFavoritePassword');
        $manager->setUserPassword($user);

        $this->assertNull($user->getPlainPassword());
        $this->assertEquals('MyEncodedPassword', $user->getPassword());
    }
}
