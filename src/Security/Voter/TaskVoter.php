<?php

namespace App\Security\Voter;

use App\Entity\Task;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskVoter extends Voter
{
    public const DELETE = 'TASK_DELETE';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [self::DELETE])
            && $subject instanceof Task;
    }

    /**
     * @param string         $attribute
     * @param Task           $subject
     * @param TokenInterface $token
     *
     * @return false
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::DELETE:
                if (
                    (null === $subject->getOwner()->getId() && $this->security->isGranted('ROLE_ADMIN')) ||
                    $user->getId() === $subject->getOwner()->getId()
                ) {
                    return true;
                }

                break;
        }

        return false;
    }
}
