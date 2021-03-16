<?php

namespace App\Tests\Security\Voter;

use App\Entity\Task;
use App\Entity\User;
use App\Security\Voter\TaskVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

final class TaskVoterTest extends TestCase
{
    public function testSupports()
    {
        $mockSecurity = $this->getMockBuilder(Security::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockToken = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $voter = new TaskVoter($mockSecurity);

        # Bad subject & bad attribute
        $this->assertEquals(0, $voter->vote($mockToken, new User(), ['TASK_FAKE']));
        # Bad subject & good attribute
        $this->assertEquals(0, $voter->vote($mockToken, new User(), [TaskVoter::DELETE]));
        # Bad subject & good attribute
        $this->assertEquals(0, $voter->vote($mockToken, new Task(), ['TASK_FAKE']));
    }

    public function testVote()
    {
        $mockSecurity = $this->getMockBuilder(Security::class)
            ->disableOriginalConstructor()
            ->getMock();

        $voter = new TaskVoter($mockSecurity);
        $task = new Task();

        # Token->getUser() do not return instanceof User
        $mockToken = $this->getMockBuilder(TokenInterface::class)->getMock();
        $mockToken->method('getUser')->willReturn(null);
        $this->assertEquals(-1, $voter->vote($mockToken, $task, [TaskVoter::DELETE]));

        # User is not Task owner
        $mockUser = $this->getMockBuilder(User::class)->getMock();
        $mockUser->method('getId')->willReturn(1);
        $mockToken = $this->getMockBuilder(TokenInterface::class)->getMock();
        $mockToken->method('getUser')->willReturn($mockUser);
        $this->assertEquals(-1, $voter->vote($mockToken, $task, [TaskVoter::DELETE]));

        # Task has no owner and user has role ROLE_ADMIN
        $mockUser = $this->getMockBuilder(User::class)->getMock();
        $mockUser->setRoles([User::ROLE_ADMIN]);
        $mockToken = $this->getMockBuilder(TokenInterface::class)->getMock();
        $mockToken->method('getUser')->willReturn($mockUser);
        $this->assertEquals(1, $voter->vote($mockToken, $task, [TaskVoter::DELETE]));

        # User is the Task owner
        $task->setOwner($mockUser);
        $this->assertEquals(1, $voter->vote($mockToken, $task, [TaskVoter::DELETE]));
    }
}