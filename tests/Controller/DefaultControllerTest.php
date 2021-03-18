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
}