<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service\Attribute;

use App\Catalog\Application\Command\CreateAttribute\CreateAttributeCommand;
use App\Catalog\Application\Command\UpdateAttribute\UpdateAttributeCommand;
use App\Catalog\Application\DTO\Attribute\AttributeOptionData;
use App\Catalog\Application\DTO\Attribute\AttributeOptionTranslationData;
use App\Catalog\Application\DTO\Attribute\AttributeTranslationData;
use App\Catalog\Application\Service\Attribute\AttributeOption\AttributeOptionMetadataProviderInterface;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\AttributeOption;
use App\Catalog\Domain\Exception\Attribute\AttributeStateException;
use App\Catalog\Domain\Exception\AttributeOption\AttributeOptionNotFoundException;
use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionUlidException;
use App\Catalog\Domain\Exception\AttributeOption\UnsupportedAttributeOptionMetadataTypeException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Attribute\Code as AttributeCode;
use App\Catalog\Domain\ValueObject\Attribute\OptionCollection;
use App\Catalog\Domain\ValueObject\Attribute\Translations as AttributeTranslations;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\Attribute\Ulid as AttributeUlid;
use App\Catalog\Domain\ValueObject\AttributeOption\ActiveFlag;
use App\Catalog\Domain\ValueObject\AttributeOption\Code as AttributeOptionCode;
use App\Catalog\Domain\ValueObject\AttributeOption\Metadata\AttributeOptionMetadataInterface;
use App\Catalog\Domain\ValueObject\AttributeOption\Translations as AttributeOptionTranslations;
use App\Catalog\Domain\ValueObject\AttributeOption\Ulid as AttributeOptionUlid;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\Service\Identity\UlidGeneratorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

final readonly class AttributeApplicationFactory implements AttributeApplicationFactoryInterface
{
    public function __construct(
        private UlidGeneratorInterface $ulidGenerator,
        #[AutowireLocator(
            services: 'catalog.product_attribute_option_metadata_provider',
            defaultIndexMethod: 'getDefaultIndexName',
        )]
        private ContainerInterface $providers,
    ) {
    }

    /**
     * @param AttributeOptionData[] $optionsData
     *
     * @return AttributeOptionUlid[]
     *
     * @throws InvalidAttributeOptionUlidException
     */
    public function mapAttributeOptionUlids(array $optionsData, bool $associative = false): array
    {
        $ulids = [];
        foreach ($optionsData as $i => $option) {
            if (null !== $option->ulid) {
                $key = $associative ? $option->ulid : $i;
                $ulids[$key] = AttributeOptionUlid::fromString($option->ulid);
            }
        }

        return $ulids;
    }

    /**
     * @throws AttributeStateException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     * @throws UnsupportedAttributeOptionMetadataTypeException
     */
    public function createFromCommand(CreateAttributeCommand $command): Attribute
    {
        $adminUlid = AdminUlid::fromString($command->adminUlid);
        $type = Type::fromString($command->type);

        return Attribute::create(
            ulid: AttributeUlid::fromString($this->ulidGenerator->next()),
            code: AttributeCode::fromString($command->code),
            type: $type,
            translations: $this->mapAttributeTranslations($command->translations),
            createdBy: $adminUlid,
            options: $this->mapOptions(optionsData: $command->options, adminUlid: $adminUlid, type: $type),
        );
    }

    /**
     * @throws AttributeStateException
     * @throws AttributeOptionNotFoundException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     * @throws UnsupportedAttributeOptionMetadataTypeException
     */
    public function updateFromCommand(Attribute $attribute, UpdateAttributeCommand $command): void
    {
        $adminUlid = AdminUlid::fromString($command->adminUlid);
        $type = Type::fromString($command->type);
        $newOptions = $this->syncOptions(
            attribute: $attribute,
            optionsData: $command->options,
            adminUlid: $adminUlid,
            type: $type,
        );

        $attribute->update(
            code: AttributeCode::fromString($command->code),
            type: $type,
            translations: $this->mapAttributeTranslations($command->translations),
            updatedBy: $adminUlid,
            options: $newOptions,
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
     * @param AttributeOptionData[] $optionsData
     *
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     * @throws UnsupportedAttributeOptionMetadataTypeException
     */
    private function mapOptions(array $optionsData, AdminUlid $adminUlid, Type $type): OptionCollection
    {
        return OptionCollection::fromArray(array_map(fn (AttributeOptionData $o) => AttributeOption::create(
            ulid: AttributeOptionUlid::fromString($this->ulidGenerator->next()),
            code: AttributeOptionCode::fromString($o->code),
            translations: $this->mapAttributeOptionTranslations($o->translations),
            isActive: ActiveFlag::fromBool($o->isActive),
            createdBy: $adminUlid,
            metadata: $this->getAttributeOptionMetadata($type, $o),
        ), $optionsData));
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

    /**
     * @param AttributeOptionData[] $optionsData
     *
     * @throws AttributeOptionNotFoundException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     * @throws UnsupportedAttributeOptionMetadataTypeException
     */
    private function syncOptions(
        Attribute $attribute,
        array $optionsData,
        AdminUlid $adminUlid,
        Type $type,
    ): OptionCollection {
        $currentOptions = $attribute->getOptions();
        $processedUlids = [];
        $syncedOptions = [];

        foreach ($optionsData as $data) {
            $code = AttributeOptionCode::fromString($data->code);
            $translations = $this->mapAttributeOptionTranslations($data->translations);
            $isActive = ActiveFlag::fromBool($data->isActive);
            $metadata = $this->getAttributeOptionMetadata($type, $data);

            if (null !== $data->ulid) {
                $option = $currentOptions->getByUlid($data->ulid)
                    ?? throw AttributeOptionNotFoundException::withUlid($data->ulid);

                $option->update(
                    code: $code,
                    translations: $translations,
                    isActive: $isActive,
                    updatedBy: $adminUlid,
                    metadata: $metadata,
                );
            } else {
                $option = AttributeOption::create(
                    ulid: AttributeOptionUlid::fromString($this->ulidGenerator->next()),
                    code: $code,
                    translations: $translations,
                    isActive: $isActive,
                    createdBy: $adminUlid,
                    metadata: $metadata,
                );
            }

            $processedUlids[$option->getUlid()->value()] = true;
            $syncedOptions[] = $option;
        }

        foreach ($currentOptions as $existingOption) {
            if (!isset($processedUlids[$existingOption->getUlid()->value()])) {
                $existingOption->deactivate($adminUlid);
                $syncedOptions[] = $existingOption;
            }
        }

        return OptionCollection::fromArray($syncedOptions);
    }

    /**
     * @throws UnsupportedAttributeOptionMetadataTypeException
     */
    private function getAttributeOptionMetadata(
        Type $type,
        AttributeOptionData $data,
    ): ?AttributeOptionMetadataInterface {
        if (!$type->hasOptionMetadata()) {
            return null;
        }

        try {
            $id = $type->value()->value;

            if (!$this->providers->has($id)) {
                throw new UnsupportedAttributeOptionMetadataTypeException(sprintf('Container does not have a metadata provider for attribute option type: %s', $id));
            }

            $provider = $this->providers->get($id);

            if (!$provider instanceof AttributeOptionMetadataProviderInterface) {
                throw new UnsupportedAttributeOptionMetadataTypeException(sprintf('Metadata provider "%s" must implement %s', $id, AttributeOptionMetadataProviderInterface::class));
            }

            return $provider->handle($data);
        } catch (ContainerExceptionInterface $e) {
            throw new UnsupportedAttributeOptionMetadataTypeException(message: 'Fail get metadata provider', previous: $e);
        }
    }
}
