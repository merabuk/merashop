<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

use App\Shared\Domain\Exception\InvalidPhoneNumberException;
use App\Shared\Domain\Exception\PhoneNumberFormatException;
use App\Shared\Domain\Exception\PhoneNumberMaxLengthException;

final class PhoneNumberValidator
{
    /**
     * @throws InvalidPhoneNumberException
     */
    public static function validate(string $phoneNumber, int $maxLength): string
    {
        $trimmedPhoneNumber = preg_replace('/\D/', '', $phoneNumber);

        $trimmedPhoneNumber = '+'.$trimmedPhoneNumber;

        if (mb_strlen($trimmedPhoneNumber) > $maxLength) {
            PhoneNumberMaxLengthException::becauseValueIsToLong($maxLength);
        }

        // only Ukraine phone numbers support yet
        if (!preg_match('/^\+380\d{9}$/', $trimmedPhoneNumber, $matches)) {
            throw PhoneNumberFormatException::becauseItIsNotValidPhoneNumberFormat($phoneNumber);
        }

        return $trimmedPhoneNumber;
    }
}
