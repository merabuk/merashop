<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\Entity;

use App\Catalog\Domain\Entity\TemporaryImage;
use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;
use App\Catalog\Domain\ValueObject\TemporaryImage\Context;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;
use App\Tests\Catalog\Support\TemporaryImageMother;
use App\Tests\Shared\BaseUnitTest;

final class TemporaryImageTest extends BaseUnitTest
{
    public function testItCreatesTemporaryImage(): void
    {
        $ulid = Ulid::fromString(TemporaryImageMother::DEFAULT_ULID);
        $path = RelativeFilePath::fromString('path/to/file/image.jpg');
        $context = Context::fromEnum(ContextEnum::ProductMain);

        $temporaryImage = TemporaryImage::create(
            ulid: $ulid,
            path: $path,
            context: $context,
        );

        self::assertNull($temporaryImage->getId());
        self::assertTrue($temporaryImage->getUlid()->equals($ulid));
        self::assertTrue($temporaryImage->getPath()->equals($path));
        self::assertTrue($temporaryImage->getContext()->equals($context));
    }
}
