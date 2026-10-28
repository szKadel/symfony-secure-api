<?php

namespace App\Controller;

use App\Application\Task\CreateTask\CreateTaskCommand;
use App\Application\Task\CreateTask\CreateTaskHandler;
use App\Application\Task\CreateTask\Validation\CreateTaskRequestValidator;
use App\Application\Task\DeleteTask\DeleteTaskHandler;
use App\Application\Task\Dto\TaskResponse;
use App\Application\Task\GetTask\GetTaskHandler;
use App\Application\Task\GetTasks\GetTasksHandler;
use App\Application\Task\UpdateTask\UpdateTaskHandler;
use App\Application\Task\UpdateTask\Validation\UpdateTaskRequestValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class TaskController extends AbstractController
{
    #[Route('/api/tasks', name: 'api_tasks_list', methods: ['GET'])]
    public function list(GetTasksHandler $handler): JsonResponse
    {
        $tasks = $handler();

        $response = array_map(
            static fn ($task): array => TaskResponse::fromEntity($task)->toArray(),
            $tasks
        );

        return $this->json($response);
    }

    #[Route('/api/tasks/{id}', name: 'api_tasks_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, GetTaskHandler $handler): JsonResponse
    {
        $task = $handler($id);

        if ($task === null) {
            throw new NotFoundHttpException('Task not found');
        }

        return $this->json(
            TaskResponse::fromEntity($task)->toArray()
        );
    }
    #[Route('/api/tasks', name: 'api_tasks_create', methods: ['POST'])]
    public function create(
        Request $request,
        CreateTaskRequestValidator $validator,
        CreateTaskHandler $handler,
    ): JsonResponse {
        $dto = $validator->validate($request);
        $task = $handler(CreateTaskCommand::fromRequest($dto));

        return $this->json(
            TaskResponse::fromEntity($task)->toArray(),
            Response::HTTP_CREATED
        );
    }


    #[Route(
        '/api/tasks/{id}',
        name: 'api_tasks_update',
        requirements: ['id' => '\d+'],
        methods: ['PATCH']
    )]
    public function update(
        int $id,
        Request $request,
        UpdateTaskRequestValidator $validator,
        UpdateTaskHandler $handler,
    ): JsonResponse {
        $command = $validator->validate($request, $id);

        $task = $handler($command);

        return $this->json(
            TaskResponse::fromEntity($task)->toArray()
        );
    }

    #[Route(
        '/api/tasks/{id}',
        name: 'api_tasks_delete',
        requirements: ['id' => '\d+'],
        methods: ['DELETE']
    )]
    public function delete(int $id, DeleteTaskHandler $handler): Response
    {
        $handler($id);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

}
