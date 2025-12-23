<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

use App\Shared\Domain\Exception\InvalidUuidException;
use Symfony\Component\Uid\Uuid;

class UuidValidator
{
    /**
     * @throws InvalidUuidException
     */
    public static function validateV7(string $uuidV7): string
    {
        if (!Uuid::isValid($uuidV7)) {
            throw InvalidUuidException::becauseItIsNotAValidUuidV7($uuidV7);
        }

        return $uuidV7;
    }
}
