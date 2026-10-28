<?php

namespace App\Application\Task\CreateTask\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateTaskRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        #[Assert\Length(max: 255)]
        public mixed $title = null,

        #[Assert\Type('string')]
        public mixed $description = null,
    ) {
    }
}
