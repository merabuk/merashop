<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

use App\Shared\Domain\Exception\InvalidUlidException;
use Symfony\Component\Uid\Ulid;

class UlidValidator
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
}
