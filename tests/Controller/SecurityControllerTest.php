<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Test\AppWebTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends AppWebTestCase
{
    use ReloadDatabaseTrait;

    public function testCsrfToken()
    {
        $client = static::createClient();
        $client->followRedirects();

        $crawler = $client->request('GET', '/login');
        $this->assertEquals(1, $crawler->filter('input[name="_csrf_token"]')->count());

        $crawler = $client->submitForm(
            'Se connecter',
            [
                'username' => 'test',
                'password' => 'test',
                '_csrf_token' => 'fake'
            ]
        );
        $this->assertEquals('Jeton CSRF invalide.', $crawler->filter('div[role="alert"]')->text());
    }

    public function testUserNotFound()
    {
        $client = static::createClient();
        $client->followRedirects();

        $client->request('GET', '/login');

        $crawler = $client->submitForm('Se connecter', ['username' => 'test', 'password' => 'test']);
        $this->assertEquals('Nom d\'utilisateur introuvable', $crawler->filter('div[role="alert"]')->text());
    }

    public function testUserBadCredentials()
    {
        $client = static::createClient();
        $client->followRedirects();

        $this->createUser('TestUsername', 'password');

        $client->request('GET', '/login');

        $crawler = $client->submitForm('Se connecter', ['username' => 'TestUsername', 'password' => 'fake']);
        $this->assertEquals('Identifiants invalides.', $crawler->filter('div[role="alert"]')->text());
    }

    public function testUserAuthenticationSuccess()
    {
        $client = static::createClient();
        $client->followRedirects();

        $this->createUser('TestUsername', 'password');

        $client->request('GET', '/login');

        $crawler = $client->submitForm('Se connecter', ['username' => 'TestUsername', 'password' => 'password']);
        $this->assertEquals(1, $crawler->filter('a:contains("Se déconnecter")')->count());
    }

    /**
     * A not authenticated user is redirected to the login page.
     */
    public function redirectAnonymousToLogin()
    {
        $client = self::createClient();
        $client->followRedirects();

        $crawler = $client->request('GET', '/');
        $this->assertEquals('/login', $crawler->getUri());
    }

    public function testUserRightAccess()
    {
        $client = static::createClient();

        $this->createUserAndLogIn($client, 'TestAdmin', 'MyStrong$Password', User::ROLE_ADMIN);
        $client->request('GET', '/users');
        $this->assertResponseIsSuccessful();

        $this->createUserAndLogIn($client, 'TestUser', 'MyStrong$Password', User::ROLE_USER);
        $client->request('GET', '/users');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
