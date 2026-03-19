<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine\Helper;

use App\Shared\Domain\ValueObject\Identity\Ulid as DomainUlid;
use Symfony\Component\Uid\Ulid as SymfonyUlid;

final readonly class UlidPersistenceHelper
{
    /**
     * Converts an array of domain-based ULIDs to an array of RFC4122 strings (UUID format).
     *
     * @param array<int, DomainUlid|string> $ulids
     *
     * @return string[]
     */
    public static function toBaseStrings(array $ulids): array
    {
        return array_map(static fn (DomainUlid|string $u) => self::toBaseString($u), $ulids);
    }

    /**
     * Convert ulid string or domain-based ULID to RFC4122 string.
     */
    public static function toBaseString(DomainUlid|string $ulid): string
    {
        $value = $ulid instanceof DomainUlid ? $ulid->value() : $ulid;

        return SymfonyUlid::fromString($value)->toRfc4122();
    }
}
