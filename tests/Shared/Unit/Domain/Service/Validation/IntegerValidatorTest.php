<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\Service\Validation;

use App\Shared\Domain\Exception\Services\IntegerIsNotUnsignedException;
use App\Shared\Domain\Service\Validation\IntegerValidator;
use PHPUnit\Framework\TestCase;

final class IntegerValidatorTest extends TestCase
{
    public function testItValidatesPositiveInteger(): void
    {
        self::assertSame(1, IntegerValidator::validateUnsigned(value: 1));
        self::assertSame(99999, IntegerValidator::validateUnsigned(value: 99999));
    }

    public function testThrowsExceptionOnZero(): void
    {
        $this->expectException(IntegerIsNotUnsignedException::class);
        IntegerValidator::validateUnsigned(value: 0);
    }

    public function testThrowsExceptionOnNegativeInteger(): void
    {
        $this->expectException(IntegerIsNotUnsignedException::class);
        IntegerValidator::validateUnsigned(value: -10);
    }
}
