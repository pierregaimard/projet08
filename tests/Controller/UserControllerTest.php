<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Test\AppWebTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class UserControllerTest extends AppWebTestCase
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
        $client = static::createClient();

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
     */
    public function testUserRolePersistance(string $username, string $role)
    {
        $client = static::createClient();
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

    public function testUsersAdminButtonAccess()
    {
        $client = static::createClient();

        # ROLE_USER tests
        $this->createUserAndLogIn($client, 'TestUser', 'MyStrong$Password', User::ROLE_USER);

        $crawler = $client->request('GET', '/');
        $this->assertEquals(0, $crawler->filter('a:contains("Liste des utilisateurs")')->count());

        # ROLE_ADMIN tests
        $this->createUserAndLogIn($client, 'TestAdmin', 'MyStrong$Password', User::ROLE_ADMIN);

        $crawler = $client->request('GET', '/');
        $this->assertEquals(1, $crawler->filter('a:contains("Liste des utilisateurs")')->count());

        $crawler = $client->request('GET', '/users');
        $this->assertEquals(1, $crawler->filter('a:contains("Ajouter un utilisateurs")')->count());
    }

    public function testUserViolationConstraints()
    {
        $client = static::createClient();
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

        $this->assertEquals(1, $crawler->filter('li:contains("doit comporter au minimum 3 caractères")')->count());
        $this->assertEquals(1, $crawler->filter('li:contains("Ce mot de passe n\'est pas assez fort.")')->count());
        $this->assertEquals(1, $crawler->filter('li:contains("format de l\'adresse n\'est pas correcte.")')->count());
    }
}
