<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Enum\Category\StatusEnum;
use App\Catalog\Domain\Factory\Contract\CategoryFactoryInterface;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Path;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Catalog\Domain\ValueObject\Category\SortOrder;
use App\Catalog\Domain\ValueObject\Category\Status;
use App\Catalog\Domain\ValueObject\Category\Translations;
use App\Catalog\Domain\ValueObject\Category\Ulid;
use App\Catalog\Domain\ValueObject\Category\Version;
use App\Shared\Domain\Enum\LocaleEnum;
use App\Shared\Domain\Service\Identity\UlidGeneratorInterface;
use Faker\Factory;
use Faker\Generator;
use phpDocumentor\Reflection\DocBlock\Description;

final readonly class CategoryMother
{
    public const string DEFAULT_ULID = '01KK1M3EG2SX64KJQ3GK3S9G0B';
    public const string DEFAULT_ADMIN_ULID = '01KHVRCA679BJ6PBXX5N3G6RR5';

    public function __construct(
        private CategoryFactoryInterface $categoryFactory,
        private UlidGeneratorInterface $ulidGenerator,
        private Generator $faker,
        private Factory $fakerFactory,
    ) {
    }

    /**
     * @param ?array<string, array{name: string, description?: string}> $translations
     */
    public static function createWithData(
        ?string $ulid = null,
        ?int $parentId = null,
        ?string $path = null,
        ?string $slug = null,
        ?int $sortOrder = null,
        ?StatusEnum $status = null,
        ?array $translations = null,
        ?int $version = null,
        ?string $createdByUlid = null,
        ?string $updatedByUlid = null,
        ?int $id = null,
    ): Category {
        $slug ??= 'test-slug';

        return new Category(
            ulid: Ulid::fromString($ulid ?? self::DEFAULT_ULID),
            parentId: $parentId ? Id::fromInt($parentId) : null,
            path: Path::fromString($path ?? Path::SEPARATOR.$slug),
            slug: Slug::fromString($slug),
            sortOrder: SortOrder::fromInt($sortOrder ?? 0),
            status: Status::fromEnum($status ?? StatusEnum::Active),
            translations: $translations
                ? Translations::fromArray($translations)
                : Translations::fromArray(self::makeFakeTranslations()),
            version: $version ? Version::fromInt($version) : Version::initial(),
            createdBy: AdminUlid::fromString($createdByUlid ?? self::DEFAULT_ADMIN_ULID),
            updatedBy: $updatedByUlid ? AdminUlid::fromString($updatedByUlid) : null,
            id: $id ? Id::fromInt($id) : null,
        );
    }

    /**
     * @param ?array<string, array{name: string, description?: string}> $translations
     */
    public function create(
        ?string $ulid = null,
        ?int $parentId = null,
        ?string $path = null,
        ?string $slug = null,
        ?int $sortOrder = null,
        ?StatusEnum $status = null,
        ?array $translations = null,
        ?string $createdByUlid = null,
    ): Category {
        $slug ??= $this->faker->unique()->slug();

        return $this->categoryFactory->createForTest(
            ulid: $ulid ?? $this->ulidGenerator->next(),
            parentId: $parentId,
            path: $path ?? Path::SEPARATOR.$slug,
            slug: $slug,
            sortOrder: $sortOrder ?? 0,
            status: $status ?? StatusEnum::Active,
            translations: $translations ?? $this->makeTranslations(),
            createdByUlid: $createdByUlid ?? $this->ulidGenerator->next(),
        );
    }

    /**
     * @return Category[]
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
     * @return array<string, array{name: string, description?: string}>
     */
    private function makeTranslations(): array
    {
        $locales = LocaleEnum::cases();
        $translations = [];

        foreach ($locales as $locale) {
            $faker = $this->fakerFactory->create($locale->value);

            $translations[$locale->value] = self::makeTranslationItem(
                name: $faker->word(),
                description: $faker->sentence(),
            );
        }

        return $translations;
    }

    private static function makeFakeTranslations(): array
    {
        $locales = LocaleEnum::cases();
        $translations = [];

        foreach ($locales as $locale) {
            $translations[$locale->value] = self::makeTranslationItem(
                name: $locale->value.' name',
                description: $locale->value.' description',
            );
        }

        return $translations;
    }

    private static function makeTranslationItem(
        string $name,
        string $description,
    ): array {
        return [
            'name' => $name,
            'description' => $description,
        ];
    }
}
