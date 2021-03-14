<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class TaskTest extends TestCase
{
    public function testTaskAnonymousOwner()
    {
        $task = new Task();
        # If no owner set Task returns an anonymous owner who is instanceof User
        $this->assertTrue($task->getOwner() instanceof User);
        $this->assertEquals(Task::OWNER_ANONYMOUS, $task->getOwner()->getUsername());

        $user = new User();
        $user->setUsername('TaskOwner');
        $task->setOwner($user);
        # If owner is set, this one is returned.
        $this->assertEquals('TaskOwner', $task->getOwner()->getUsername());
    }
}
