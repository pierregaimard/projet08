<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Test\AbstractAppWebTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

final class SecurityControllerTest extends AbstractAppWebTestCase
{
    use ReloadDatabaseTrait;

    public function testCsrfToken()
    {
        $client = self::createClient();
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
        $this->assertStringContainsString(
            'Jeton CSRF invalide.',
            $crawler->filter('div[role="alert"]')->text(null, false)
        );
    }

    public function testUserNotFound()
    {
        $client = self::createClient();
        $client->followRedirects();

        $client->request('GET', '/login');

        $crawler = $client->submitForm('Se connecter', ['username' => 'test', 'password' => 'test']);
        $this->assertStringContainsString(
            'Nom d\'utilisateur introuvable',
            $crawler->filter('div[role="alert"]')->text(null, false)
        );
    }

    public function testUserBadCredentials()
    {
        $client = self::createClient();
        $client->followRedirects();

        $this->createUser('TestUsername', 'password');

        $client->request('GET', '/login');

        $crawler = $client->submitForm('Se connecter', ['username' => 'TestUsername', 'password' => 'fake']);
        $this->assertStringContainsString(
            'Identifiants invalides.',
            $crawler->filter('div[role="alert"]')->text(null, false)
        );
    }

    public function testUserAuthenticationSuccess()
    {
        $client = self::createClient();
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
        $client = self::createClient();

        $this->createUserAndLogIn($client, 'TestAdmin', 'MyStrong$Password', User::ROLE_ADMIN);
        $client->request('GET', '/users');
        $this->assertResponseIsSuccessful();

        $this->createUserAndLogIn($client, 'TestUser', 'MyStrong$Password', User::ROLE_USER);
        $client->request('GET', '/users');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * Checks if authenticated user who navigates to login page is redirected automatically to the homepage.
     */
    public function testAuthenticatedUserRedirection()
    {
        $client = self::createClient();
        $client->followRedirects();

        $this->createUserAndLogIn($client, 'TestUser', 'MyStrong$Password', User::ROLE_USER);

        $crawler = $client->request('GET', '/login');
        $this->assertStringContainsString('Bienvenue sur Todo List', $crawler->filter('h1')->text(null, false));
    }

    public function testUserLogout()
    {
        $client = self::createClient();
        $client->followRedirects();

        $this->createUserAndLogIn($client, 'TestUser', 'MyStrong$Password', User::ROLE_USER);
        $crawler = $client->request('GET', '/logout');

        # User is redirected to the login page
        $this->assertEquals(1, $crawler->filter('button:contains("Se connecter")')->count());

        $crawler = $client->request('GET', '/');
        # User is redirected to the login page
        $this->assertEquals(1, $crawler->filter('button:contains("Se connecter")')->count());
    }
}
