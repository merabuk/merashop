<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Exception\Traits;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

trait UnprocessableEntityErrorTrait
{
    protected function makeSystemValidationException(
        string $message,
        mixed $value,
        ConstraintViolationListInterface $violations,
    ): UnprocessableEntityHttpException {
        return new UnprocessableEntityHttpException(
            message: $message,
            previous: new ValidationFailedException(
                value: $value,
                violations: $violations
            )
        );
    }
}
