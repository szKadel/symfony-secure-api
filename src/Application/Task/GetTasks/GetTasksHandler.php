<?php

namespace App\Application\Task\GetTasks;

use App\Entity\User;
use App\Repository\TaskRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final readonly class GetTasksHandler
{
    public function __construct(
        private TaskRepository $taskRepository,
        private Security $security,
    ) {
    }

    public function __invoke(): array
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedHttpException('Authentication required');
        }

        return $this->taskRepository->findBy(
            ['owner' => $user],
            ['createdAt' => 'DESC']
        );
    }
}
