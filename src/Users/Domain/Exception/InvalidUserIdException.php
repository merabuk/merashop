<?php

declare(strict_types=1);

namespace App\Users\Domain\Exception;

class InvalidUserIdException extends InvalidUserValueObjectExceptionInterface
{
    public static function becauseItIsNotAValidId(): self
    {
        return new self('The value is not a valid User ID');
    }
}
