<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service\Validation;

use App\Shared\Domain\Exception\Services\Identity\InvalidUlidException;

final class UlidValidator
{
    // ULID: 26 chars, Crockford's base32
    private const string REGEX = '/^[0-7][0-9A-HJKMNP-TV-Z]{25}$/i';

    /**
     * @throws InvalidUlidException
     */
    public static function validate(string $ulid): string
    {
        if (!preg_match(self::REGEX, $ulid)) {
            throw InvalidUlidException::becauseItIsNotAValidUlid($ulid);
        }

        return $ulid;
    }
}
