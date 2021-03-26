<?php

namespace App\Tests\Repository;

use App\Entity\Task;
use App\Entity\User;
use App\Test\AbstractAppWebTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserRepositoryTest extends AbstractAppWebTestCase
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

        $mockUserInterface = $this->createMock(UserInterface::class);

        $this->expectException(UnsupportedUserException::class);
        $userRepository->upgradePassword($mockUserInterface, 'FakePassword');
    }
}
