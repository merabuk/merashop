<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

use App\Shared\Domain\Exception\IntegerIsNotUnsignedException;

final class IntegerValidator
{
    /**
     * @throws IntegerIsNotUnsignedException
     */
    public static function validateUnsigned(int $value): int
    {
        if ($value <= 0) {
            throw IntegerIsNotUnsignedException::becauseValueIsNotUnsigned();
        }

        return $value;
    }
}
