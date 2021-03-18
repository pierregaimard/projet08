<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Test\AppWebTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

final class DefaultControllerTest extends AppWebTestCase
{
    use ReloadDatabaseTrait;

    public function testHomepage()
    {
        $client = self::createClient();
        $this->createUserAndLogIn($client, 'TestUser', 'MyStrong$Password', User::ROLE_USER);
        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Bienvenue sur Todo List', $crawler->filter('h1')->text(null, false));
    }

    /**
     * @dataProvider urlProvider
     *
     * @param string $url
     */
    public function testHomepageLinks(string $url)
    {
        $client = self::createClient();
        $this->createUserAndLogIn($client, 'TestUser', 'MyStrong$Password', User::ROLE_USER);
        $client->request('GET', $url);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function urlProvider()
    {
        return [
            ['/tasks/create'],
            ['/tasks/todo'],
            ['/tasks/done']
        ];
    }
}