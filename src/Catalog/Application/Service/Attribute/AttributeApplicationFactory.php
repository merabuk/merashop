<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service\Attribute;

use App\Catalog\Application\Command\CreateAttribute\CreateAttributeCommand;
use App\Catalog\Application\Command\UpdateAttribute\UpdateAttributeCommand;
use App\Catalog\Application\DTO\Attribute\AttributeOptionData;
use App\Catalog\Application\DTO\Attribute\AttributeOptionTranslationData;
use App\Catalog\Application\DTO\Attribute\AttributeTranslationData;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\AttributeOption;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Attribute\Code as AttributeCode;
use App\Catalog\Domain\ValueObject\Attribute\OptionCollection;
use App\Catalog\Domain\ValueObject\Attribute\Translations as AttributeTranslations;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\Attribute\Ulid as AttributeUlid;
use App\Catalog\Domain\ValueObject\AttributeOption\ActiveFlag;
use App\Catalog\Domain\ValueObject\AttributeOption\Code as AttributeOptionCode;
use App\Catalog\Domain\ValueObject\AttributeOption\Translations as AttributeOptionTranslations;
use App\Catalog\Domain\ValueObject\AttributeOption\Ulid as AttributeOptionUlid;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\Service\Identity\UlidGeneratorInterface;

final readonly class AttributeApplicationFactory implements AttributeApplicationFactoryInterface
{
    public function __construct(
        private UlidGeneratorInterface $ulidGenerator,
    ) {
    }

    /**
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function createFromCommand(CreateAttributeCommand $command): Attribute
    {
        $createdBy = AdminUlid::fromString($command->adminUlid);

        return Attribute::create(
            ulid: AttributeUlid::fromString($this->ulidGenerator->next()),
            code: AttributeCode::fromString($command->code),
            type: Type::fromString($command->type),
            translations: $this->mapAttributeTranslations($command->translations),
            createdBy: $createdBy,
            options: $this->mapOptions($command->options, $createdBy),
        );
    }

    /**
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function updateFromCommand(Attribute $attribute, UpdateAttributeCommand $command): void
    {
        $attribute->update(
            code: AttributeCode::fromString($command->code),
            translations: $this->mapAttributeTranslations($command->translations),
            updatedBy: AdminUlid::fromString($command->adminUlid),
        );
    }

    /**
     * @param AttributeTranslationData[] $translations
     *
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    private function mapAttributeTranslations(array $translations): AttributeTranslations
    {
        return AttributeTranslations::fromArray(array_map(fn (AttributeTranslationData $t) => [
            'name' => $t->name,
        ], $translations));
    }

    /**
     * @param AttributeOptionData[] $options
     *
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    private function mapOptions(array $options, AdminUlid $createdBy): OptionCollection
    {
        return OptionCollection::fromArray(array_map(fn (AttributeOptionData $o) => AttributeOption::create(
            ulid: AttributeOptionUlid::fromString($this->ulidGenerator->next()),
            code: AttributeOptionCode::fromString($o->code),
            translations: $this->mapAttributeOptionTranslations($o->translations),
            isActive: ActiveFlag::fromBool($o->isActive),
            createdBy: $createdBy,
        ), $options));
    }

    /**
     * @param AttributeOptionTranslationData[] $translations
     *
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    private function mapAttributeOptionTranslations(array $translations): AttributeOptionTranslations
    {
        return AttributeOptionTranslations::fromArray(array_map(fn (AttributeOptionTranslationData $t) => [
            'value' => $t->value,
        ], $translations));
    }
}
