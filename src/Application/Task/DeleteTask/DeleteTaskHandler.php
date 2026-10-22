<?php

namespace App\Application\Task\DeleteTask;

use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class DeleteTaskHandler
{
    public function __construct(
        private TaskRepository $taskRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(int $id): void
    {
        $task = $this->taskRepository->find($id);

        if ($task === null) {
            throw new NotFoundHttpException('Task not found');
        }

        $this->entityManager->remove($task);
        $this->entityManager->flush();
    }
}
