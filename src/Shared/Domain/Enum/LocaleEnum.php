<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enum;

/**
 * ISO 639-1.
 */
enum LocaleEnum: string
{
    use StringEnumTrait;

    case En = 'en';
    case Uk = 'uk';

    public static function default(): self
    {
        return self::En; // TODO: replace to Uk in future
    }
}
