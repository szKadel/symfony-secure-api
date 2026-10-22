<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TaskControllerTest extends WebTestCase
{
    public function testCreateTask(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/tasks',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'title' => 'Learn Symfony',
                'description' => 'Write functional tests',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $data = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertSame('Learn Symfony', $data['title']);
        self::assertSame('Write functional tests', $data['description']);
        self::assertFalse($data['isCompleted']);
        self::assertIsInt($data['id']);
        self::assertArrayHasKey('createdAt', $data);
    }

    public function testCreateTaskWithBlankTitle(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/tasks',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: '{"title":""}'
        );

        self::assertResponseStatusCodeSame(422);
    }

    public function testCreateTaskWithInvalidJson(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/tasks',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: '{"title":'
        );

        self::assertResponseStatusCodeSame(400);
    }
}
