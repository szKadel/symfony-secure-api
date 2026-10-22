<?php

namespace App\Application\Task\GetTask;

use App\Entity\Task;
use App\Repository\TaskRepository;

final readonly class GetTaskHandler
{
    public function __construct(
        private TaskRepository $taskRepository,
    ) {
    }

    public function __invoke(int $id): ?Task
    {
        return $this->taskRepository->find($id);
    }
}
