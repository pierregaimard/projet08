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
        $this->assertTrue($task->getOwner() instanceof User);
        $this->assertEquals(Task::OWNER_ANONYMOUS, $task->getOwner()->getUsername());
    }
}
