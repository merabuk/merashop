<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service\Validation;

use App\Shared\Domain\Exception\Services\Validation\EmailAddressFormatException;
use App\Shared\Domain\Exception\Services\Validation\EmailAddressMaxLengthException;
use App\Shared\Domain\Exception\Services\Validation\InvalidEmailAddressException;

final class EmailValidator
{
    public const int LOCAL_PART_MAX_LENGTH = 64;
    public const int DOMAIN_PART_MAX_LENGTH = 63;

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

        $atPosition = mb_strpos($trimmedEmail, '@');
        if (false === $atPosition) {
            throw EmailAddressFormatException::becauseItIsNotValidEmailAddress($trimmedEmail);
        }

        $localPart = mb_substr($trimmedEmail, 0, $atPosition);
        if (mb_strlen($localPart) > self::LOCAL_PART_MAX_LENGTH) {
            throw EmailAddressFormatException::becauseLocalPartIsTooLong(self::LOCAL_PART_MAX_LENGTH);
        }

        return $trimmedEmail;
    }
}
