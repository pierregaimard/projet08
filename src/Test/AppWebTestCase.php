<?php

namespace App\Test;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
     * @return SessionInterface
     */
    private function getSession(): SessionInterface
    {
        return static::$container->get('session');
    }

    /**
     * @param string      $username
     * @param string      $password
     * @param string|null $role
     *
     * @return User
     */
    protected function createUser(string $username, string $password, string $role = null): User
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail(sprintf('%s@test.fr', $username));
        $user->setPassword(
            static::$container->get('security.user_password_encoder.generic')->encodePassword($user, $password)
        );
        if (null !== $role) {
            $user->setRoles([$role]);
        }

        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }
}
