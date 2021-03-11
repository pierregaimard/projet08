<?php

namespace App\Tests\Controller;

use App\Test\AppWebTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

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

        $crawler = $client->submitForm('Se connecter', ['username' => 'test', 'password' => 'test',]);
        $this->assertEquals('Nom d\'utilisateur introuvable', $crawler->filter('div[role="alert"]')->text());
    }
}
