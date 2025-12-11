<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

use App\Shared\Domain\Exception\InvalidPhoneNumberException;

final class PhoneNumberValidator
{
    /**
     * @throws InvalidPhoneNumberException
     */
    public static function validate(string $phoneNumber): string
    {
        $trimmedPhoneNumber = preg_replace('/\D/', '', $phoneNumber);

        $trimmedPhoneNumber = '+'.$trimmedPhoneNumber;

        // only Ukraine phone numbers support yet
        if (!preg_match('/^\+380\d{9}$/', $trimmedPhoneNumber, $matches)) {
            throw InvalidPhoneNumberException::becauseItIsNotValidPhoneNumberFormat($phoneNumber);
        }

        return $trimmedPhoneNumber;
    }
}
