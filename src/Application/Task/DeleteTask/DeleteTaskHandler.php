<?php

namespace App\Application\Task\DeleteTask;

use App\Entity\User;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class DeleteTaskHandler
{
    public function __construct(
        private TaskRepository $taskRepository,
        private EntityManagerInterface $entityManager,
        private Security $security,
    ) {
    }

    public function __invoke(int $id): void
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedHttpException('Authentication required');
        }

        $task = $this->taskRepository->findOneBy([
            'id' => $id,
            'owner' => $user,
        ]);

        if ($task === null) {
            throw new NotFoundHttpException('Task not found');
        }

        $this->entityManager->remove($task);
        $this->entityManager->flush();
    }
}
