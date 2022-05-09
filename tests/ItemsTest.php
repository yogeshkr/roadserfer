<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Item;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class ItemsTest extends ApiTestCase
{
    use ReloadDatabaseTrait;

    public function testLogin(): void
    {
        $response = static::createClient()->request('POST', '/api/login', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'username' => 'test@example.com',
                'password' => '$3CR3T',
            ],
        ]);

        $json = $response->toArray();
        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', $json);
        $this->assertResponseIsSuccessful();

        // test not authorized
        static::createClient()->request('GET', '/api/items');
        $this->assertResponseStatusCodeSame(401);

        // test authorized
        static::createClient()->request('GET', '/api/items', ['auth_bearer' => $json['token']]);
        $this->assertResponseIsSuccessful();
    }
}