<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserPasswordManager
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private UserPasswordEncoderInterface $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param User $user
     */
    public function setUserPassword(User $user): void
    {
        if (null === $user->getPlainPassword()) {
            return;
        }

        $user->setPassword($this->encoder->encodePassword($user, $user->getPlainPassword()));
        $user->eraseCredentials();
    }
}
