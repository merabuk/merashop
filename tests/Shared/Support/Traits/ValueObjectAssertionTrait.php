<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use PHPUnit\Framework\Assert;

trait ValueObjectAssertionTrait
{
    protected function assertVoEqualsOrNull(?EquatableInterface $expected, ?EquatableInterface $actual): void
    {
        if (null === $expected) {
            Assert::assertNull($actual, 'Expected ValueObject is null, but actual is not');

            return;
        }

        Assert::assertNotNull($actual, 'Expected ValueObject is set, but actual is null');
        Assert::assertTrue($expected->equals($actual), 'ValueObjects are not equal');
    }
}
