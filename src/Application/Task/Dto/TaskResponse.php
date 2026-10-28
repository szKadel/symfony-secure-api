<?php

namespace App\Application\Task\Dto;

use App\Entity\Task;

final readonly class TaskResponse
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description,
        public bool $isCompleted,
        public string $createdAt,
    ) {
    }

    public static function fromEntity(Task $task): self
    {
        return new self(
            id: $task->getId(),
            title: $task->getTitle(),
            description: $task->getDescription(),
            isCompleted: $task->isCompleted(),
            createdAt: $task->getCreatedAt()->format(DATE_ATOM),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'isCompleted' => $this->isCompleted,
            'createdAt' => $this->createdAt,
        ];
    }
}
