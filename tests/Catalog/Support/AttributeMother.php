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
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public static function createWithData(array $overrides = []): Attribute
    {
        return new Attribute(
            id: Id::fromInt($overrides['id'] ?? 123),
            ulid: Ulid::fromString($overrides['ulid'] ?? '01KHVRCA0FCCYAQT1P88R317DD'),
            code: Code::fromString($overrides['code'] ?? 'test-code'),
            type: isset($overrides['type']) && $overrides['type'] instanceof TypeEnum
                ? Type::fromEnum($overrides['type'])
                : Type::string(),
            translations: isset($overrides['translations'])
                ? Translations::fromArray($overrides['translations'])
                : Translations::fromArray(self::makeFakeTranslations()),
            version: isset($overrides['version'])
                ? Version::fromInt($overrides['version'])
                : Version::initial(),
            createdBy: AdminUlid::fromString($overrides['adminUlid'] ?? '01KHVRCA679BJ6PBXX5N3G6RR5'),
            updatedBy: isset($overrides['updatedBy'])
                ? AdminUlid::fromString($overrides['updatedBy'])
                : null
        );
    }

    /**
     * @param array<string, mixed> $overrides
     */
    public function create(array $overrides = []): Attribute
    {
        return $this->attributeFactory->createForTest(
            ulid: $overrides['ulid'] ?? $this->ulidGenerator->next(),
            code: $overrides['code'] ?? $this->faker->unique()->word(),
            type: $overrides['type'] ?? $this->faker->randomElement(TypeEnum::cases()),
            translations: $overrides['translations'] ?? $this->makeTranslations(),
            adminUlid: $overrides['adminUlid'] ?? $this->ulidGenerator->next(),
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
