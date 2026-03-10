<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support;

use App\Catalog\Domain\Entity\TemporaryImage;
use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;
use App\Catalog\Domain\Factory\Contract\TemporaryImageFactoryInterface;
use App\Catalog\Domain\ValueObject\TemporaryImage\Context;
use App\Catalog\Domain\ValueObject\TemporaryImage\Id;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid;
use App\Shared\Domain\Service\UlidGeneratorInterface;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;
use Faker\Generator;

final readonly class TemporaryImageMother
{
    public const string DEFAULT_ULID = '01KKBQ0KQ2YE88QE4PFCVSJA97';

    public function __construct(
        private TemporaryImageFactoryInterface $temporaryImageFactory,
        private UlidGeneratorInterface $ulidGenerator,
        private Generator $faker,
    ) {
    }

    public static function createWithData(
        ?string $ulid = null,
        ?string $path = null,
        ?ContextEnum $context = null,
        ?int $id = null,
    ): TemporaryImage {
        return new TemporaryImage(
            ulid: Ulid::fromString($ulid ?? self::DEFAULT_ULID),
            path: RelativeFilePath::fromString($path ?? 'temp/image.jpg'),
            context: Context::fromEnum($context ?? ContextEnum::ProductMain),
            id: $id ? Id::fromInt($id) : null,
        );
    }

    public function create(
        ?string $ulid = null,
        ?string $path = null,
        ?ContextEnum $context = null,
    ): TemporaryImage {
        return $this->temporaryImageFactory->createForTest(
            ulid: $ulid ?? $this->ulidGenerator->next(),
            path: $path ?? 'temp/image.jpg',
            context: $context ?? $this->faker->randomElement(ContextEnum::cases()),
        );
    }

    /**
     * @return TemporaryImage[]
     */
    public function createMany(int $count): array
    {
        $attributes = [];
        for ($i = 0; $i < $count; ++$i) {
            $attributes[] = $this->create();
        }

        return $attributes;
    }
}
