<?php

namespace App\Controller;

use App\Application\Task\CreateTask\CreateTaskCommand;
use App\Application\Task\CreateTask\CreateTaskHandler;
use App\Application\Task\CreateTask\Validation\CreateTaskRequestValidator;
use App\Application\Task\Dto\TaskResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TaskController extends AbstractController
{
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
}
