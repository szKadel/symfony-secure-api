<?php

namespace App\Tests\Controller;

use App\Application\Task\DeleteTask\DeleteTaskHandler;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TaskControllerTest extends WebTestCase
{

    private function authenticateClient(KernelBrowser $client): void
    {
        $container = static::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);

        $user = new User();
        $user->setEmail('test-' . bin2hex(random_bytes(8)) . '@example.com');
        $user->setPassword('test-password-hash');

        $entityManager->persist($user);
        $entityManager->flush();

        $jwtManager = $container->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        $client->setServerParameter(
            'HTTP_AUTHORIZATION',
            'Bearer ' . $token
        );
    }


    public function testCreateTask(): void
    {
        $client = static::createClient();

        $this->authenticateClient($client);

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

        $this->authenticateClient($client);

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

        $this->authenticateClient($client);

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

        $this->authenticateClient($client);

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

        $this->authenticateClient($client);

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

        $this->authenticateClient($client);

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

        $this->authenticateClient($client);

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

        $this->authenticateClient($client);

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


    public function testDeleteTaskReturns204AndRemovesTask(): void
    {
        $client = static::createClient();

        $this->authenticateClient($client);

        $client->request(
            'POST',
            '/api/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"title":"Task to delete in test"}'
        );

        self::assertResponseStatusCodeSame(201);

        $task = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $client->request('DELETE', '/api/tasks/' . $task['id']);

        self::assertResponseStatusCodeSame(204);
        self::assertSame('', $client->getResponse()->getContent());

        $client->request('GET', '/api/tasks/' . $task['id']);

        self::assertResponseStatusCodeSame(404);
    }

    public function testDeleteNonExistingTaskReturns404(): void
    {
        $client = static::createClient();

        $this->authenticateClient($client);

        $client->request('DELETE', '/api/tasks/999999');

        self::assertResponseStatusCodeSame(404);

        $data = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertSame('Task not found', $data['error']);
    }

    public function testGetTasksWithoutTokenReturns401(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/tasks');

        self::assertResponseStatusCodeSame(401);
    }


    public function testUserCannotReadAnotherUsersTask(): void
    {
        $client = static::createClient();

        $this->authenticateClient($client);

        $client->request(
            'POST',
            '/api/tasks',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: '{"title":"Private task"}'
        );

        self::assertResponseStatusCodeSame(201);

        $task = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->authenticateClient($client);

        $client->request(
            'GET',
            '/api/tasks/' . $task['id']
        );

        self::assertResponseStatusCodeSame(404);
    }


    public function testUserCannotModifyOrDeleteAnotherUsersTask(): void
    {
        $client = static::createClient();

        $this->authenticateClient($client);

        $client->request(
            'POST',
            '/api/tasks',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: '{"title":"Private task"}'
        );

        self::assertResponseStatusCodeSame(201);

        $task = json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $taskUrl = '/api/tasks/' . $task['id'];

        $this->authenticateClient($client);

        $client->request(
            'PATCH',
            $taskUrl,
            server: ['CONTENT_TYPE' => 'application/json'],
            content: '{"title":"Hacked task"}'
        );

        self::assertResponseStatusCodeSame(404);

        $client->request('DELETE', $taskUrl);

        self::assertResponseStatusCodeSame(404);
    }


}
