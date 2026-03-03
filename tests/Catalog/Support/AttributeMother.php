<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Service\AttributeFactoryInterface;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Id;
use App\Catalog\Domain\ValueObject\Attribute\Translations;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;
use App\Catalog\Domain\ValueObject\Attribute\Version;
use App\Shared\Domain\Enum\LocaleEnum;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\Service\UlidGeneratorInterface;
use Faker\Factory;
use Faker\Generator;

final readonly class AttributeMother
{
    public function __construct(
        private AttributeFactoryInterface $attributeFactory,
        private UlidGeneratorInterface $ulidGenerator,
        private Generator $faker,
        private Factory $fakerFactory,
    ) {
    }

    /**
     * Static method for Unit-tests.
     *
     * @param ?array<string, array{name: string}> $translations
     *
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public static function createWithData(
        ?string $ulid = null,
        ?string $code = null,
        ?TypeEnum $type = null,
        ?array $translations = null,
        ?int $version = null,
        ?string $createdByUlid = null,
        ?string $updatedByUlid = null,
        ?int $id = null,
    ): Attribute {
        return new Attribute(
            id: $id ? Id::fromInt($id) : null,
            ulid: Ulid::fromString($ulid ?? '01KHVRCA0FCCYAQT1P88R317DD'),
            code: Code::fromString($code ?? 'test-code'),
            type: $type ? Type::fromEnum($type) : Type::string(),
            translations: $translations
                ? Translations::fromArray($translations)
                : Translations::fromArray(self::makeFakeTranslations()),
            version: $version ? Version::fromInt($version) : Version::initial(),
            createdBy: AdminUlid::fromString($createdByUlid ?? '01KHVRCA679BJ6PBXX5N3G6RR5'),
            updatedBy: $updatedByUlid ? AdminUlid::fromString($updatedByUlid) : null
        );
    }

    /**
     * @param ?array<string, array{name: string}> $translations
     */
    public function create(
        ?string $ulid = null,
        ?string $code = null,
        ?TypeEnum $type = null,
        ?array $translations = null,
        ?string $createdByUlid = null,
    ): Attribute {
        return $this->attributeFactory->createForTest(
            ulid: $ulid ?? $this->ulidGenerator->next(),
            code: $code ?? $this->faker->unique()->word(),
            type: $type ?? $this->faker->randomElement(TypeEnum::cases()),
            translations: $translations ?? $this->makeTranslations(),
            createdByUlid: $createdByUlid ?? $this->ulidGenerator->next(),
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

    /**
     * @return array<string, array{name: string}>
     */
    private function makeTranslations(): array
    {
        $locales = LocaleEnum::cases();
        $translations = [];

        foreach ($locales as $locale) {
            $faker = $this->fakerFactory->create($locale->value);

            $translations[$locale->value] = self::makeTranslationItem($faker->word());
        }

        return $translations;
    }

    private static function makeFakeTranslations(): array
    {
        $locales = LocaleEnum::cases();
        $translations = [];

        foreach ($locales as $locale) {
            $translations[$locale->value] = self::makeTranslationItem($locale->value.' name');
        }

        return $translations;
    }

    private static function makeTranslationItem(string $name): array
    {
        return ['name' => $name];
    }
}
