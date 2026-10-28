<?php

namespace App\Application\Task\UpdateTask\Validation;

use App\Application\Task\UpdateTask\Dto\UpdateTaskRequest;
use App\Application\Task\UpdateTask\UpdateTaskCommand;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class UpdateTaskRequestValidator
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {
    }

    public function validate(Request $request, int $id): UpdateTaskCommand
    {
        try {
            $data = json_decode(
                $request->getContent(),
                false,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException) {
            throw new BadRequestHttpException('Invalid JSON');
        }

        if (!$data instanceof \stdClass) {
            throw new BadRequestHttpException('JSON body must be an object');
        }

        $fields = get_object_vars($data);

        $allowedFields = ['title', 'description', 'isCompleted'];

        foreach (array_keys($fields) as $field) {
            if (!in_array($field, $allowedFields, true)) {
                throw new UnprocessableEntityHttpException(
                    sprintf('Unknown field: %s', $field)
                );
            }
        }

        if ($fields === []) {
            throw new UnprocessableEntityHttpException(
                'At least one field must be provided'
            );
        }

        if (array_key_exists('title', $fields)) {
            if (!is_string($fields['title']) || trim($fields['title']) === '') {
                throw new UnprocessableEntityHttpException(
                    'Title must be a non-empty string'
                );
            }

            $fields['title'] = trim($fields['title']);
        }

        $dto = new UpdateTaskRequest(
            title: $fields['title'] ?? null,
            description: $fields['description'] ?? null,
            isCompleted: $fields['isCompleted'] ?? null,
        );

        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            $errors = [];

            foreach ($violations as $violation) {
                $errors[] = [
                    'field' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }

            throw new UnprocessableEntityHttpException(
                json_encode($errors, JSON_THROW_ON_ERROR)
            );
        }

        return new UpdateTaskCommand(
            id: $id,
            title: $dto->title,
            description: $dto->description,
            isCompleted: $dto->isCompleted,
            updateTitle: array_key_exists('title', $fields),
            updateDescription: array_key_exists('description', $fields),
            updateIsCompleted: array_key_exists('isCompleted', $fields),
        );
    }
}
