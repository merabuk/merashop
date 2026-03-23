<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Factory;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\AttributeOption;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\Attribute\AttributeOptionUniqueException;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeCodeException;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeNameException;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeOptionItemException;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeUlidException;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeVersionException;
use App\Catalog\Domain\Exception\InvalidAdminUlidException;
use App\Catalog\Domain\Factory\Contract\AttributeFactoryInterface;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\OptionCollection;
use App\Catalog\Domain\ValueObject\Attribute\Translations;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;

final readonly class AttributeFactory implements AttributeFactoryInterface
{
    /**
     * @param array<string, array{name: string}> $translations
     * @param AttributeOption[]                  $options
     *
     * @throws AttributeOptionUniqueException
     * @throws InvalidAdminUlidException
     * @throws InvalidAttributeCodeException
     * @throws InvalidAttributeNameException
     * @throws InvalidAttributeVersionException
     * @throws InvalidAttributeUlidException
     * @throws InvalidLocaleException
     * @throws InvalidAttributeOptionItemException
     */
    public function createForTest(
        string $ulid,
        string $code,
        TypeEnum $type,
        array $translations,
        string $createdByUlid,
        array $options = [],
    ): Attribute {
        return Attribute::create(
            ulid: Ulid::fromString($ulid),
            code: Code::fromString($code),
            type: Type::fromEnum($type),
            translations: Translations::fromArray($translations),
            createdBy: AdminUlid::fromString($createdByUlid),
            options: OptionCollection::fromArray($options),
        );
    }
}
