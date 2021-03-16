<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Test\AppWebTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

final class TaskControllerTest extends AppWebTestCase
{
    use RefreshDatabaseTrait;

    public function testTaskList()
    {
        $client = self::createClient();

        $_em = $this->getEntityManager();

        $this->logIn($client, $_em->getRepository(User::class)->findOneBy(['username' => 'Admin']));
        $crawler = $client->request('GET', '/tasks');
        $this->assertEquals(10, $crawler->filter('div.thumbnail')->count());
    }

    public function testTaskCreate()
    {
        $client = self::createClient();
        $client->followRedirects();

        $_em = $this->getEntityManager();
        $user = $_em->getRepository(User::class)->findOneBy(['username' => 'User']);
        $this->logIn($client, $user);

        $client->request('GET', '/tasks/create');
        $crawler = $client->submitForm(
            'Ajouter',
            ['task[title]' => 'myTask', 'task[content]' => 'This is an amazing task!']
        );

        # Task is successfully persisted
        $this->assertEquals(
            1, $crawler->filter('div.alert-success:contains("La tâche a été bien été ajoutée.")')->count()
        );
        $task = $_em->getRepository(Task::class)->findOneBy(['title' => 'myTask']);
        # Task owner is authenticated user
        $this->assertTrue($task->getOwner()->getId() === $user->getId());

        $client->request('GET', '/tasks/create');
        $crawler = $client->submitForm('Ajouter');

        # Empty form returns constraints violations
        $this->assertEquals(1, $crawler->filter('li:contains("Vous devez saisir un titre.")')->count());
        $this->assertEquals(1, $crawler->filter('li:contains("Vous devez saisir du contenu.")')->count());
    }

    public function testTaskEdit()
    {
        $client = self::createClient();
        $client->followRedirects();

        $_em = $this->getEntityManager();
        $user = $_em->getRepository(User::class)->findOneBy(['username' => 'Admin']);
        $this->logIn($client, $user);

        $crawler = $client->request('GET', '/tasks');
        $client->click($crawler->filterXPath('//a[@href="/tasks/1/edit"]')->link());

        $crawler = $client->submitForm(
            'Modifier',
            ['task[title]' => 'My New Title', 'task[content]' => 'My new content'],
        );

        $task = $this->getEntityManager()->getRepository(Task::class)->find(1);

        # User has been redirected to task list
        $this->assertEquals(
            1,
            $crawler->filter('div.alert-success:contains("La tâche a bien été modifiée.")')->count()
        );
        # Task content has been updated
        $this->assertEquals('My New Title', $task->getTitle());
        $this->assertEquals('My new content', $task->getContent());
        # Task owner has not changed
        $this->assertFalse($task->getOwner()->getId() === $user->getId());
    }
}
