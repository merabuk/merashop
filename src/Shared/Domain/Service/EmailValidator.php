<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

use App\Shared\Domain\Exception\InvalidEmailAddressException;

final class EmailValidator
{
    /**
     * @throws InvalidEmailAddressException
     */
    public static function validate(string $email): string
    {
        $trimmedEmail = mb_trim($email);

        if (!filter_var($trimmedEmail, FILTER_VALIDATE_EMAIL)) {
            throw InvalidEmailAddressException::becauseItIsNotValidEmailAddress($trimmedEmail);
        }

        return $trimmedEmail;
    }
}
