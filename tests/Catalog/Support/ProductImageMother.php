<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support;

use App\Catalog\Domain\Entity\ProductImage;
use App\Catalog\Domain\Factory\Contract\ProductImageFactoryInterface;
use App\Catalog\Domain\ValueObject\ProductImage\Id;
use App\Catalog\Domain\ValueObject\ProductImage\MainImageFlag;
use App\Catalog\Domain\ValueObject\ProductImage\SortOrder;
use App\Catalog\Domain\ValueObject\ProductImage\Ulid;
use App\Shared\Domain\Service\Identity\UlidGeneratorInterface;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;
use Faker\Generator;

final readonly class ProductImageMother
{
    public const string DEFAULT_ULID = '01KKTSW8P5VC0YSJQN8CD4E1Y6';

    public function __construct(
        private ProductImageFactoryInterface $productImageFactory,
        private UlidGeneratorInterface $ulidGenerator,
        private Generator $faker,
    ) {
    }

    public static function createWithData(
        ?string $ulid = null,
        ?string $path = null,
        ?int $sortOrder = null,
        ?bool $isMain = null,
        ?int $id = null,
    ): ProductImage {
        return new ProductImage(
            ulid: Ulid::fromString($ulid ?? self::DEFAULT_ULID),
            path: RelativeFilePath::fromString($path ?? 'test/image.jpg'),
            sortOrder: SortOrder::fromInt($sortOrder ?? 0),
            isMain: MainImageFlag::fromBool($isMain ?? false),
            id: $id ? Id::fromInt($id) : null,
        );
    }

    public function create(
        ?string $ulid = null,
        ?string $path = null,
        ?int $sortOrder = null,
        ?bool $isMain = null,
    ): ProductImage {
        return $this->productImageFactory->createForTest(
            ulid: $ulid ?? $this->ulidGenerator->next(),
            path: $path ?? 'test/image.jpg',
            sortOrder: $sortOrder ?? $this->faker->numberBetween(0, 100),
            isMain: $isMain ?? $this->faker->boolean(),
        );
    }
}
