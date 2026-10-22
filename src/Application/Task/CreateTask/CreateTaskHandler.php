<?php

namespace App\Application\Task\CreateTask;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CreateTaskHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(CreateTaskCommand $command): Task
    {
        $task = new Task();
        $task->setTitle($command->title);
        $task->setDescription($command->description);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task;
    }
}
