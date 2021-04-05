<?php

namespace App\Tests\Error;

use App\Test\AbstractAppWebTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

final class ErrorPagesTest extends AbstractAppWebTestCase
{
    use RefreshDatabaseTrait;

    public function test404ErrorPage()
    {
        $client = self::createClient();
        $this->createUserAndLogIn($client, 'TestUser', 'MyStrong$Password');

        $client->request('GET', '/non-existent-page');
        $this->assertResponseStatusCodeSame(404);
    }

    public function test403ErrorPage()
    {
        $client = self::createClient();
        $this->createUserAndLogIn($client, 'TestUser', 'MyStrong$Password');

        $client->request('GET', '/users');
        $this->assertResponseStatusCodeSame(403);
    }
}
