<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\AttributeOption;
use App\Catalog\Domain\Enum\Attribute\TypeEnum as AttributeTypeEnum;
use App\Catalog\Domain\Factory\Contract\AttributeOptionFactoryInterface;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\AttributeOption\ActiveFlag;
use App\Catalog\Domain\ValueObject\AttributeOption\Code;
use App\Catalog\Domain\ValueObject\AttributeOption\Id;
use App\Catalog\Domain\ValueObject\AttributeOption\Metadata\AttributeOptionMetadataInterface;
use App\Catalog\Domain\ValueObject\AttributeOption\Metadata\DimensionMetadata;
use App\Catalog\Domain\ValueObject\AttributeOption\Translations;
use App\Catalog\Domain\ValueObject\AttributeOption\Ulid;
use App\Catalog\Domain\ValueObject\AttributeOption\Version;
use App\Shared\Domain\Enum\LocaleEnum;
use App\Shared\Domain\Service\Identity\UlidGeneratorInterface;
use Faker\Factory;
use Faker\Generator;

final readonly class AttributeOptionMother
{
    public const string DEFAULT_ULID = '01KMDD4E8PXVFES2WN8K2MBT0P';
    public const string DEFAULT_ADMIN_ULID = '01KHVRCA679BJ6PBXX5N3G6RR5';

    public function __construct(
        private AttributeOptionFactoryInterface $attributeOptionFactory,
        private UlidGeneratorInterface $ulidGenerator,
        private Generator $faker,
        private Factory $fakerFactory,
    ) {
    }

    /**
     * @param ?array<string, array{value: string}> $translations
     */
    public static function createWithData(
        ?string $ulid = null,
        ?string $code = null,
        ?array $translations = null,
        ?bool $isActive = null,
        ?int $version = null,
        ?string $createdByUlid = null,
        ?string $updatedByUlid = null,
        ?AttributeTypeEnum $attributeType = null,
        mixed $metadata = null,
        ?int $id = null,
    ): AttributeOption {
        return new AttributeOption(
            ulid: Ulid::fromString($ulid ?? self::DEFAULT_ULID),
            code: Code::fromString($code ?? 'test-option-code'),
            translations: Translations::fromArray($translations ?: self::makeFakeTranslations()),
            isActive: ActiveFlag::fromBool($isActive ?? true),
            version: $version ? Version::fromInt($version) : Version::initial(),
            createdBy: AdminUlid::fromString($createdByUlid ?? self::DEFAULT_ADMIN_ULID),
            metadata: self::makeFakeMetadata($attributeType, $metadata),
            updatedBy: $updatedByUlid ? AdminUlid::fromString($updatedByUlid) : null,
            id: $id ? Id::fromInt($id) : null,
        );
    }

    /**
     * @param ?array<string, array{value: string}> $translations
     */
    public function create(
        ?string $ulid = null,
        ?string $code = null,
        ?array $translations = null,
        ?bool $isActive = null,
        ?string $createdByUlid = null,
        ?AttributeTypeEnum $attributeType = null,
        mixed $metadata = null,
    ): AttributeOption {
        return $this->attributeOptionFactory->createForTest(
            ulid: $ulid ?? $this->ulidGenerator->next(),
            code: $code ?? $this->faker->unique()->word(),
            translations: $translations ?? $this->makeTranslations(),
            isActive: $isActive ?? $this->faker->boolean(),
            createdByUlid: $createdByUlid ?? $this->ulidGenerator->next(),
            metadata: $this->makeMetadata($attributeType, $metadata),
        );
    }

    /**
     * @return Attribute[]
     */
    public function createMany(int $count): array
    {
        $attributes = [];
        for ($i = 0; $i < $count; ++$i) {
            $attributes[] = $this->create();
        }

        return $attributes;
    }

    private function makeTranslations(): array
    {
        $locales = LocaleEnum::cases();
        $translations = [];

        foreach ($locales as $locale) {
            $faker = $this->fakerFactory->create($locale->value);

            $translations[$locale->value] = self::makeTranslationItem($faker->text());
        }

        return $translations;
    }

    private static function makeFakeTranslations(): array
    {
        $locales = LocaleEnum::cases();
        $translations = [];

        foreach ($locales as $locale) {
            $translations[$locale->value] = self::makeTranslationItem($locale->value.' value');
        }

        return $translations;
    }

    private static function makeTranslationItem(string $value): array
    {
        return ['value' => $value];
    }

    private function makeMetadata(
        ?AttributeTypeEnum $attributeType,
        mixed $metadata,
    ): ?AttributeOptionMetadataInterface {
        return match ($attributeType) {
            AttributeTypeEnum::Dimension => DimensionMetadata::fromFloat(
                (float) ($metadata ?? $this->faker->randomFloat(2, 0, 100))
            ),
            default => null,
        };
    }

    private static function makeFakeMetadata(
        ?AttributeTypeEnum $attributeType,
        mixed $metadata,
    ): ?AttributeOptionMetadataInterface {
        return match ($attributeType) {
            AttributeTypeEnum::Dimension => DimensionMetadata::fromNullableFloat(
                is_float($metadata) ? $metadata : null
            ),
            default => null,
        };
    }
}
