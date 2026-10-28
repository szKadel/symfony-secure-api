<?php

namespace App\Application\Task\UpdateTask;

final readonly class UpdateTaskCommand
{
    public function __construct(
        public int $id,
        public ?string $title,
        public ?string $description,
        public ?bool $isCompleted,
        public bool $updateTitle,
        public bool $updateDescription,
        public bool $updateIsCompleted,
    ) {
    }
}
