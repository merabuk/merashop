<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Infrastructure\Service\Identity;

use App\Shared\Domain\Service\Validation\UuidValidator;
use App\Shared\Infrastructure\Service\Identity\SymfonyUuidGenerator;
use App\Tests\Shared\BaseUnitTest;

final class SymfonyUuidGeneratorTest extends BaseUnitTest
{
    public function testUuidGeneratorProducesValidV7(): void
    {
        $generator = new SymfonyUuidGenerator();
        $uuid = $generator->nextV7();

        self::assertNotEmpty($uuid);
        self::assertSame($uuid, UuidValidator::validateV7($uuid));
    }
}
