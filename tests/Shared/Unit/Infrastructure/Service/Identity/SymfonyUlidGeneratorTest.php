<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Infrastructure\Service\Identity;

use App\Shared\Domain\Service\Validation\UlidValidator;
use App\Shared\Infrastructure\Service\Identity\SymfonyUlidGenerator;
use App\Tests\Shared\BaseUnitTest;

final class SymfonyUlidGeneratorTest extends BaseUnitTest
{
    public function testUlidGeneratorProducesValidOutput(): void
    {
        $generator = new SymfonyUlidGenerator();
        $ulid = $generator->next();

        self::assertNotEmpty($ulid);
        self::assertSame($ulid, UlidValidator::validate($ulid));
    }
}
