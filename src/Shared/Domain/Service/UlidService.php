<?php

namespace App\Shared\Domain\Service;

use App\Shared\Domain\Exception\InvalidUlidException;
use Symfony\Component\Uid\Ulid;

class UlidService
{
    /**
     * @throws InvalidUlidException
     */
    public static function validate(string $ulid): string
    {
        if (!Ulid::isValid($ulid)) {
            throw InvalidUlidException::becauseItIsNotAValidUlid($ulid);
        }

        return $ulid;
    }

    public static function generate(): string
    {
        return Ulid::generate();
    }
}
