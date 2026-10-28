<?php

namespace App\Application\Task\UpdateTask\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateTaskRequest
{
    public function __construct(
        #[Assert\Type('string')]
        #[Assert\Length(max: 255)]
        public mixed $title = null,

        #[Assert\Type('string')]
        public mixed $description = null,

        #[Assert\Type('bool')]
        public mixed $isCompleted = null,
    ) {
    }
}
