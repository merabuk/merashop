<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use App\Shared\Domain\ValueObject\EquatableInterface;
use PHPUnit\Framework\Assert;

trait ValueObjectAssertionTrait
{
    protected function assertVoEqualsOrNull(?EquatableInterface $expected, ?EquatableInterface $actual, string $message = ''): void
    {
        if (null === $expected) {
            Assert::assertNull($actual, $message ?: 'Expected ValueObject is null, but actual is not');

            return;
        }

        Assert::assertNotNull($actual, $message ?: 'Expected ValueObject is set, but actual is null');
        Assert::assertTrue($expected->equals($actual), $message ?: 'ValueObjects are not equal');
    }
}
