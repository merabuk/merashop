<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

use App\Shared\Domain\Exception\EmailAddressFormatException;
use App\Shared\Domain\Exception\EmailAddressMaxLengthException;
use App\Shared\Domain\Exception\InvalidEmailAddressException;

final class EmailValidator
{
    /**
     * @throws InvalidEmailAddressException
     */
    public static function validate(string $email, int $maxLength): string
    {
        $trimmedEmail = mb_trim($email);

        if (mb_strlen($trimmedEmail) > $maxLength) {
            throw EmailAddressMaxLengthException::becauseValueIsToLong($maxLength);
        }

        // TODO improve validation in future
        if (!filter_var($trimmedEmail, FILTER_VALIDATE_EMAIL)) {
            throw EmailAddressFormatException::becauseItIsNotValidEmailAddress($trimmedEmail);
        }

        return $trimmedEmail;
    }
}
