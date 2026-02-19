<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Service\AttributeFactoryInterface;
use App\Shared\Domain\Enum\LocaleEnum;
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

            $translations[$locale->value] = [
                'name' => $faker->word(),
            ];
        }

        return $translations;
    }
}
