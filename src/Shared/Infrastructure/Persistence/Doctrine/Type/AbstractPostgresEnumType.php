<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine\Type;

use BackedEnum;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class AbstractPostgresEnumType extends Type
{
    /**
     * @return class-string<BackedEnum>
     */
    abstract protected function getEnumClass(): string;

    abstract public function getEnumName(): string;

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $this->getEnumName();
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        return (string) $value;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?BackedEnum
    {
        if (null === $value) {
            return null;
        }

        $enumClass = $this->getEnumClass();

        return $enumClass::from($value);
    }

    public function getName(): string
    {
        return $this->getEnumName();
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
