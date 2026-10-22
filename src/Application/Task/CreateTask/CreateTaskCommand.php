<?php

namespace App\Application\Task\CreateTask;

use App\Application\Task\CreateTask\Dto\CreateTaskRequest;

final readonly class CreateTaskCommand
{
    public function __construct(
        public string $title,
        public ?string $description = null,
    ) {
    }

    public static function fromRequest(CreateTaskRequest $request): self
    {
        return new self(
            title: trim($request->title),
            description: $request->description,
        );
    }
}
