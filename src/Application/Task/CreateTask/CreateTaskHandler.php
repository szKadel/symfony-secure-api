<?php

namespace App\Application\Task\CreateTask;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class CreateTaskHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private readonly Security $security,
    ) {
    }

    public function __invoke(CreateTaskCommand $command): Task
    {
        $task = new Task();
        $task->setTitle($command->title);
        $task->setDescription($command->description);

        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException(
                'Authentication required'
            );
        }

        $task->setOwner($user);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task;
    }
}
