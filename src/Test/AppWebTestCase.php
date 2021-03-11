<?php

namespace App\Test;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppWebTestCase extends WebTestCase
{
    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return static::$container->get('doctrine')->getManager();
    }

    /**
     * @param string          $username
     * @param string          $password
     * @param array           $roles
     *
     * @return User
     */
    protected function createUser(string $username, string $password, array $roles = []): User
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail(sprintf('%s@test.fr', $username));
        $user->setPassword(
            static::$container->get('security.user_password_encoder.generic')->encodePassword($user, $password)
        );
        $user->setRoles($roles);

        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }
}
