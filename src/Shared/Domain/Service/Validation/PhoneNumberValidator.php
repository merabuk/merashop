<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service\Validation;

use App\Shared\Domain\Exception\Services\Validation\InvalidPhoneNumberException;
use App\Shared\Domain\Exception\Services\Validation\PhoneNumberFormatException;
use App\Shared\Domain\Exception\Services\Validation\PhoneNumberMaxLengthException;

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
            throw PhoneNumberMaxLengthException::becauseValueIsToLong($maxLength);
        }

        // only Ukraine phone numbers support yet
        if (!preg_match('/^\+380\d{9}$/', $trimmedPhoneNumber, $matches)) {
            throw PhoneNumberFormatException::becauseItIsNotValidPhoneNumberFormat($phoneNumber);
        }

        return $trimmedPhoneNumber;
    }
}
