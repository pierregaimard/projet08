<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Test\AbstractAppWebTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

final class UserControllerTest extends AbstractAppWebTestCase
{
    use ReloadDatabaseTrait;

    /**
     * Check if user role choice is present in user create form.
     *
     * @dataProvider urlsProvider
     *
     * @param string $url
     */
    public function testUserRoleChoiceInCreateForm(string $url)
    {
        $client = self::createClient();

        $this->createUserAndLogIn($client, 'MyUser', 'MyStrong$Password', User::ROLE_ADMIN);

        $crawler = $client->request('GET', $url);
        $this->assertEquals(1, $crawler->filter('select[name="user[roles]"]')->count());
        $this->assertEquals(1, $crawler->filter('option[value="' . User::ROLE_USER . '"]')->count());
        $this->assertEquals(1, $crawler->filter('option[value="' . User::ROLE_USER . '"]')->count());
    }

    public function urlsProvider(): array
    {
        return [
            'User create' => ['/users/create'],
            'User update' => ['/users/1/edit'],
        ];
    }

    /**
     * @dataProvider usersProvider
     *
     * @param string $username
     * @param string $role
     */
    public function testUserRolePersistance(string $username, string $role)
    {
        $client = self::createClient();
        $this->createUserAndLogIn($client, 'TestAdmin', 'MyStrong$Password', User::ROLE_ADMIN);
        $client->request('GET', '/users/create');

        $client->submitForm(
            'Ajouter',
            [
                'user[username]' => $username,
                'user[plainPassword][first]' => 'MyStrong$Password',
                'user[plainPassword][second]' => 'MyStrong$Password',
                'user[email]' => $username . '@test.test',
                'user[roles]' => $role
            ]
        );

        $em = $this->getEntityManager();
        $user = $em->getRepository(User::class)->findOneBy(['username' => $username]);

        $this->assertEquals([$role], $user->getRoles());
    }

    public function usersProvider(): array
    {
        return [
            'Simple user' => ['SimpleUser', User::ROLE_USER],
            'Admin user' => ['AdminUser', User::ROLE_ADMIN],
        ];
    }

    public function testUserList()
    {
        $client = self::createClient();
        $this->createUserAndLogIn($client, 'AdminUser', 'MyStrong$Password', User::ROLE_ADMIN);

        $crawler = $client->request('GET', '/users');
        $this->assertEquals(11, $crawler->filter('tbody tr')->count());
    }

    public function testUsersAdminButtonAccess()
    {
        $client = self::createClient();

        # ROLE_USER tests
        $this->createUserAndLogIn($client, 'TestUser', 'MyStrong$Password', User::ROLE_USER);

        $crawler = $client->request('GET', '/');
        $this->assertEquals(0, $crawler->filter('a:contains("Liste des utilisateurs")')->count());

        # ROLE_ADMIN tests
        $this->createUserAndLogIn($client, 'TestAdmin', 'MyStrong$Password', User::ROLE_ADMIN);

        $crawler = $client->request('GET', '/');
        $this->assertEquals(1, $crawler->filter('a:contains("Utilisateurs")')->count());

        $crawler = $client->request('GET', '/users');
        $this->assertEquals(1, $crawler->filter('a:contains("Ajouter un utilisateur")')->count());
    }

    public function testUserCreationViolationConstraints()
    {
        $client = self::createClient();
        $client->followRedirects();
        $this->createUserAndLogIn($client, 'TestAdmin', 'MyStrong$Password', User::ROLE_ADMIN);
        $client->request('GET', '/users/create');

        $crawler = $client->submitForm(
            'Ajouter',
            [
                'user[username]' => 'aa',
                'user[plainPassword][first]' => 'badPass',
                'user[plainPassword][second]' => 'badPass',
                'user[email]' => 'invalidEmail',
                'user[roles]' => 'ROLE_USER'
            ]
        );

        $this->assertEquals(
            1,
            $crawler->filter('span.form-error-message:contains("doit comporter au minimum 3 caractères")')->count()
        );
        $this->assertEquals(
            1,
            $crawler->filter('span.form-error-message:contains("Ce mot de passe n\'est pas assez fort.")')->count()
        );
        $this->assertEquals(
            1,
            $crawler->filter('span.form-error-message:contains("format de l\'adresse n\'est pas correcte.")')->count()
        );
    }

    public function testUserEditionViolationConstraints()
    {
        $client = self::createClient();
        $client->followRedirects();
        $this->createUserAndLogIn($client, 'TestAdmin', 'MyStrong$Password', User::ROLE_ADMIN);
        $this->createUser('TestUser', 'MyStrong$Password', User::ROLE_USER);
        $client->request('GET', '/users/2/edit');

        $crawler = $client->submitForm(
            'Modifier',
            [
                'user[username]' => 'aa',
                'user[plainPassword][first]' => 'MyStrong$Password',
                'user[plainPassword][second]' => 'MyFakePassword',
                'user[email]' => 'invalidEmail',
            ]
        );

        $this->assertEquals(
            1,
            $crawler->filter('span.form-error-message:contains("doit comporter au minimum 3 caractères")')->count()
        );
        $this->assertEquals(
            1,
            $crawler->filter('span.form-error-message:contains("deux mots de passe doivent correspondre.")')->count()
        );
        $this->assertEquals(
            1,
            $crawler->filter('span.form-error-message:contains("format de l\'adresse n\'est pas correcte.")')->count()
        );
    }

    public function testUserCreation()
    {
        $client = self::createClient();
        $client->followRedirects();
        $this->createUserAndLogIn($client, 'TestAdmin', 'MyStrong$Password', User::ROLE_ADMIN);

        $client->request('GET', '/users/create');

        $client->submitForm(
            'Ajouter',
            [
                'user[username]' => 'MyGoodUser',
                'user[plainPassword][first]' => 'MyStrong$Password',
                'user[plainPassword][second]' => 'MyStrong$Password',
                'user[email]' => 'good.email@test.test',
                'user[roles]' => User::ROLE_USER
            ]
        );

        $em = $this->getEntityManager();
        $user = $em->getRepository(User::class)->findOneBy(['username' => 'MyGoodUser']);

        $this->assertTrue($user instanceof User);
        $this->assertEquals('good.email@test.test', $user->getEmail());
        $this->assertEquals([User::ROLE_USER], $user->getRoles());
    }

    public function testUserEdition()
    {
        $client = self::createClient();
        $client->followRedirects();
        $this->createUserAndLogIn($client, 'TestAdmin', 'MyStrong$Password', User::ROLE_ADMIN);
        $this->createUser('TestUser', 'MyStrong$Password', User::ROLE_USER);

        $client->request('GET', '/users/2/edit');

        $client->submitForm(
            'Modifier',
            [
                'user[username]' => 'MyGoodUser',
                'user[plainPassword][first]' => 'MyStrong$Password',
                'user[plainPassword][second]' => 'MyStrong$Password',
                'user[email]' => 'new.email@test.test',
                'user[roles]' => User::ROLE_ADMIN
            ]
        );

        $em = $this->getEntityManager();
        $user = $em->getRepository(User::class)->find(2);

        $this->assertEquals('MyGoodUser', $user->getUsername());
        $this->assertEquals('new.email@test.test', $user->getEmail());
        $this->assertEquals([User::ROLE_ADMIN], $user->getRoles());
    }

    public function testUserDeletion()
    {
        $client = self::createClient();
        $client->followRedirects();

        # Create users
        $this->createUserAndLogIn($client, 'TestAdmin', 'MyStrong$Password', User::ROLE_ADMIN);
        $userToDelete = $this->createUser('TestUser', 'MyStrong$Password', User::ROLE_USER);

        # Add a task to user
        $task = new Task();
        $task->setTitle('dd');
        $task->setContent('ddd');
        $task->setOwner($userToDelete);
        $_em = $this->getEntityManager();
        $_em->persist($task);
        $_em->flush();

        # Goto users list
        $crawler = $client->request('GET', '/users');
        $deleteButton = $crawler->filterXPath('//a[contains(text(), "Supprimer")]')->last()->link();

        # Click on delete button for the second user
        $crawler = $client->click($deleteButton);
        # Check if we are in good page and has selected the good user.
        $this->assertEquals(1, $crawler->filter('h1:contains("TestUser")')->count());
        $crawler = $client->submitForm('Supprimer');

        # Check if return to homepage
        $this->assertStringContainsString(
            'Liste des utilisateurs',
            $crawler->filter('h1')->text(null, false)
        );
        $user = $this->getEntityManager()->getRepository(User::class)->findOneBy(['username' => 'TestUser']);
        # Check if user has been deleted
        $this->assertFalse($user instanceof User);
        # Check if user task has been removed too.
        $taskList = $this->getEntityManager()->getRepository(Task::class)->findBy(['owner' => $userToDelete]);
        $this->assertEmpty($taskList);
    }
}
