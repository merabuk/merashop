<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject\AdminAccount;

use App\IdentityAccess\Domain\Exception\AdminAccount\InvalidAdminAccountIdException;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Id;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class IdTest extends TestCase
{
    /**
     * @throws InvalidAdminAccountIdException
     */
    public function testItCreatesValidId(): void
    {
        $id = 123;
        $vo = Id::fromInt($id);

        self::assertSame($id, $vo->value());
        self::assertSame((string) $id, (string) $vo);
    }

    /**
     * @throws InvalidAdminAccountIdException
     */
    public function testItProvidesEqualityCheck(): void
    {
        $vo1 = Id::fromInt(123);
        $vo2 = Id::fromInt(123);
        $vo3 = Id::fromInt(321);

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }

    #[DataProvider('invalidIdProvider')]
    public function testThrowsExceptionOnInvalidInput(int $invalidValue): void
    {
        $this->expectException(InvalidAdminAccountIdException::class);
        Id::fromInt($invalidValue);
    }

    public static function invalidIdProvider(): iterable
    {
        yield 'negative' => [-1];
        yield 'zero' => [0];
    }
}
