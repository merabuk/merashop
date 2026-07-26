<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\ValueObject\Identity;

use App\Shared\Domain\Exception\Services\Validation\IntegerIsNotUnsignedException;
use App\Shared\Domain\ValueObject\Identity\UnsignedIntegerId;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\IntegerIdTestTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class UnsignedIntegerIdTest extends BaseUnitTest
{
    use IntegerIdTestTrait;

    public function testItCreatesValidId(): void
    {
        $id = 123;
        $vo = $this->getAnonymousClass($id);

        self::assertSame($id, $vo->value());
        self::assertSame((string) $id, (string) $vo);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $vo1 = $this->getAnonymousClass(123);
        $vo2 = $this->getAnonymousClass(123);
        $vo3 = $this->getAnonymousClass(321);

        $this->baseEqualityCheckAssertion(same1: $vo1, same2: $vo2, other: $vo3);
    }

    #[DataProvider('invalidIdProvider')]
    public function testThrowsExceptionOnInvalidInput(int $invalidValue): void
    {
        $this->expectException(IntegerIsNotUnsignedException::class);
        $this->getAnonymousClass($invalidValue);
    }

    private function getAnonymousClass(int $id): UnsignedIntegerId
    {
        return new readonly class($id) extends UnsignedIntegerId {
            public function __construct(int $id)
            {
                parent::__construct($id);
            }
        };
    }
}
