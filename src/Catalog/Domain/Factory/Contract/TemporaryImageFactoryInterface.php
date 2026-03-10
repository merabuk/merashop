<?php

namespace App\Catalog\Domain\Factory\Contract;

use App\Catalog\Domain\Entity\TemporaryImage;
use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;

interface TemporaryImageFactoryInterface
{
    public function createForTest(
        string $ulid,
        string $path,
        ContextEnum $context,
    ): TemporaryImage;
}
