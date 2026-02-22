<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

use App\Shared\Domain\Exception\Services\InvalidUuidException;

final class UuidValidator
{
    // UUID v7: xxxxxxxx-xxxx-7xxx-xxxx-xxxxxxxxxxxx
    private const string REGEX_V7 = '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    /**
     * @throws InvalidUuidException
     */
    public static function validateV7(string $uuidV7): string
    {
        if (!preg_match(self::REGEX_V7, $uuidV7)) {
            throw InvalidUuidException::becauseItIsNotAValidUuidV7($uuidV7);
        }

        return $uuidV7;
    }
}
