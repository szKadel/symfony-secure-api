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


    public function testGetTasksReturnsArray(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/tasks');

        self::assertResponseIsSuccessful();

        $data = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertIsArray($data);
        self::assertTrue(array_is_list($data));
    }

    public function testGetNonExistingTaskReturns404(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/tasks/999999');

        self::assertResponseStatusCodeSame(404);

        $data = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertSame('Task not found', $data['error']);
    }


    public function testPatchTaskUpdatesCompletionStatus(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'title' => 'Task for PATCH test',
                'description' => 'Original description',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);

        $createdTask = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $client->request(
            'PATCH',
            '/api/tasks/' . $createdTask['id'],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"isCompleted":true}'
        );

        self::assertResponseStatusCodeSame(200);

        $updatedTask = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertTrue($updatedTask['isCompleted']);
        self::assertSame('Task for PATCH test', $updatedTask['title']);
        self::assertSame('Original description', $updatedTask['description']);
    }

    public function testPatchTaskRejectsEmptyTitle(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"title":"Task for validation test"}'
        );

        self::assertResponseStatusCodeSame(201);

        $createdTask = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $client->request(
            'PATCH',
            '/api/tasks/' . $createdTask['id'],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"title":""}'
        );

        self::assertResponseStatusCodeSame(422);
    }

    public function testPatchNonExistingTaskReturns404(): void
    {
        $client = static::createClient();

        $client->request(
            'PATCH',
            '/api/tasks/999999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"isCompleted":true}'
        );

        self::assertResponseStatusCodeSame(404);
    }


}
