<?php

namespace App\Tests\Controller;

use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;

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
        $this->assertEquals('Invalid CSRF token.', $crawler->filter('div[role="alert"]')->text());
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
