<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Factory;

use App\Catalog\Domain\Entity\AttributeOption;
use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionCodeException;
use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionUlidException;
use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionValueException;
use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionVersionException;
use App\Catalog\Domain\Exception\InvalidAdminUlidException;
use App\Catalog\Domain\Factory\Contract\AttributeOptionFactoryInterface;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\AttributeOption\ActiveFlag;
use App\Catalog\Domain\ValueObject\AttributeOption\Code;
use App\Catalog\Domain\ValueObject\AttributeOption\Translations;
use App\Catalog\Domain\ValueObject\AttributeOption\Ulid;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;

final readonly class AttributeOptionFactory implements AttributeOptionFactoryInterface
{
    /**
     * @param array<string, array{value: string}> $translations
     *
     * @throws InvalidAttributeOptionVersionException
     * @throws InvalidAttributeOptionUlidException
     * @throws InvalidAttributeOptionCodeException
     * @throws InvalidAdminUlidException
     * @throws InvalidAttributeOptionValueException
     * @throws InvalidLocaleException
     */
    public function createForTest(
        string $ulid,
        string $code,
        array $translations,
        bool $isActive,
        string $createdByUlid,
    ): AttributeOption {
        return AttributeOption::create(
            ulid: Ulid::fromString($ulid),
            code: Code::fromString($code),
            translations: Translations::fromArray($translations),
            isActive: ActiveFlag::fromBool($isActive),
            createdBy: AdminUlid::fromString($createdByUlid),
        );
    }
}
