<?php

namespace App\Application\Task\GetTask;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final readonly class GetTaskHandler
{
    public function __construct(
        private TaskRepository $taskRepository,
        private Security $security,
    ) {
    }

    public function __invoke(int $id): ?Task
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedHttpException('Authentication required');
        }

        return $this->taskRepository->findOneBy([
            'id' => $id,
            'owner' => $user,
        ]);
    }
}
