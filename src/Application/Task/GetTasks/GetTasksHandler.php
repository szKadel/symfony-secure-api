<?php

namespace App\Application\Task\GetTasks;

use App\Entity\Task;
use App\Repository\TaskRepository;

final readonly class GetTasksHandler
{
    public function __construct(
        private TaskRepository $taskRepository,
    ) {
    }

    /**
     * @return Task[]
     */
    public function __invoke(): array
    {
        return $this->taskRepository->findBy(
            [],
            ['createdAt' => 'DESC']
        );
    }
}
