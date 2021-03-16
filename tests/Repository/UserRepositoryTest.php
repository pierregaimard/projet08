<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Test\AppWebTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

final class UserRepositoryTest extends AppWebTestCase
{
    use ReloadDatabaseTrait;

    public function testUpgradePassword()
    {
        self::bootKernel();
        $user = $this->createUser('TestUser', 'MyGoodPassword');

        $userRepository = $this->getEntityManager()->getRepository(User::class);
        $userRepository->upgradePassword($user, 'MyNewEncodedPassword');

        $user = $userRepository->findOneBy(['username' => 'TestUser']);
        $this->assertEquals('MyNewEncodedPassword', $user->getPassword());
    }
}
