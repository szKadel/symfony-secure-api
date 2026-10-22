<?php

namespace App\Application\Task\CreateTask\Validation;

use App\Application\Task\CreateTask\Dto\CreateTaskRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class CreateTaskRequestValidator
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {
    }

    public function validate(Request $request): CreateTaskRequest
    {
        try {
            $data = json_decode(
                $request->getContent(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException) {
            throw new BadRequestHttpException('Invalid JSON');
        }

        if (!is_array($data) || array_is_list($data)) {
            throw new BadRequestHttpException('Expected a JSON object');
        }

        $dto = new CreateTaskRequest(
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
        );

        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            $errors = [];

            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()][] =
                    $violation->getMessage();
            }

            throw new UnprocessableEntityHttpException(
                json_encode($errors, JSON_THROW_ON_ERROR)
            );
        }

        return $dto;
    }
}
