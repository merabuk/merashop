<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeIdException;
use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionIdException;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeDateValueException;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeLocalizedStringValueException;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeLocalizedTextValueException;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeValueIdException;
use App\Catalog\Domain\Exception\ProductAttributeValue\ProductAttributeValueStateException;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\AttributeOption\Id as AttributeOptionId;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Id as ProductAttributeValueId;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttributeOption;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProductAttributeValue;
use App\Catalog\Infrastructure\Persistence\Doctrine\Normalizer\ProductAttributeValueNormalizer;
use App\Shared\Domain\Exception\InvalidArgumentException;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Shared\Infrastructure\Persistence\Doctrine\Interface\ProxyReferenceProviderInterface;

final readonly class ProductAttributeValueMapper
{
    public function __construct(
        private ProxyReferenceProviderInterface $referenceProvider,
        private ProductAttributeValueNormalizer $normalizer,
    ) {
    }

    /**
     * @throws EntityIdMissingException
     * @throws InvalidProductAttributeValueIdException
     * @throws InvalidAttributeIdException
     * @throws InvalidAttributeOptionIdException
     * @throws InvalidProductAttributeLocalizedStringValueException
     * @throws InvalidProductAttributeLocalizedTextValueException
     * @throws ProductAttributeValueStateException
     * @throws InvalidProductAttributeDateValueException
     */
    public function toDomain(OrmProductAttributeValue $orm): ProductAttributeValue
    {
        $id = $orm->id ?? throw EntityIdMissingException::forEntity($orm::class);

        $type = $orm->attribute->type;
        $optionId = $orm->option?->id;

        if (null === $type) {
            throw $this->makeError(sprintf('%s Attribute type is null', $this->getLogPrefix($id)));
        }

        return new ProductAttributeValue(
            attributeId: AttributeId::fromInt($orm->attribute->id),
            attributeOptionId: $optionId ? AttributeOptionId::fromInt($optionId) : null,
            value: $this->normalizer->denormalize($type, $orm->valueJson),
            id: ProductAttributeValueId::fromInt($id)
        );
    }

    public function mapToExistingOrm(ProductAttributeValue $domain, OrmProductAttributeValue $orm): void
    {
        $vo = $domain->getValue();
        $option = $domain->getAttributeOptionId();

        $orm->option = match (true) {
            null !== $option => $this->referenceProvider->getReference(
                className: OrmAttributeOption::class,
                id: $option->value()
            ),
            default => null,
        };

        $orm->valueJson = $vo ? $this->normalizer->normalize($vo) : null;
    }

    private function getLogPrefix(int $id): string
    {
        return sprintf('[%s::%d]', OrmProductAttributeValue::class, $id);
    }

    private function makeError(string $message): InvalidArgumentException
    {
        throw new InvalidArgumentException($message);
    }
}
