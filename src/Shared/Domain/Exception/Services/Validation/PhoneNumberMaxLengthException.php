<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\Services\Validation;

final class PhoneNumberMaxLengthException extends InvalidPhoneNumberException
{
    public static function becauseValueIsToLong(int $maxLength): self
    {
        return new self(sprintf('Phone number is too long than %d characters', $maxLength));
    }
}
