<?php

namespace App\Application\Task\UpdateTask;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class UpdateTaskHandler
{
    public function __construct(
        private TaskRepository $taskRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(UpdateTaskCommand $command): Task
    {
        $task = $this->taskRepository->find($command->id);

        if ($task === null) {
            throw new NotFoundHttpException('Task not found');
        }

        if ($command->updateTitle) {
            $task->setTitle($command->title);
        }

        if ($command->updateDescription) {
            $task->setDescription($command->description);
        }

        if ($command->updateIsCompleted) {
            $task->setIsCompleted($command->isCompleted);
        }

        $this->entityManager->flush();

        return $task;
    }
}
