<?php

namespace App\Security\Voter;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskVoter extends Voter
{
    public const DELETE = 'DELETE';

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
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::DELETE:
                if ($this->canDelete($subject, $user)) return true;
        }

        return false;
    }

    private function canDelete(Task $subject, User $user)
    {
        if (null === $subject->getOwner()->getId() && $this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if ($user->getId() === $subject->getOwner()->getId()) {
            return true;
        }

        return false;
    }
}
