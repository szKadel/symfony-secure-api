<?php

namespace App\Application\Task\UpdateTask;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class UpdateTaskHandler
{
    public function __construct(
        private TaskRepository $taskRepository,
        private EntityManagerInterface $entityManager,
        private readonly Security $security,
    ) {
    }

    public function __invoke(UpdateTaskCommand $command): Task
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedHttpException('Authentication required');
        }

        $task = $this->taskRepository->findOneBy([
            'id' => $command->id,
            'owner' => $user,
        ]);

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
