<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Infrastructure\Service;

use App\Shared\Domain\Service\UuidValidator;
use App\Shared\Infrastructure\Service\SymfonyUuidGenerator;
use PHPUnit\Framework\TestCase;

final class SymfonyUuidGeneratorTest extends TestCase
{
    public function testUuidGeneratorProducesValidV7(): void
    {
        $generator = new SymfonyUuidGenerator();
        $uuid = $generator->nextV7();

        self::assertNotEmpty($uuid);
        self::assertSame($uuid, UuidValidator::validateV7($uuid));
    }
}
