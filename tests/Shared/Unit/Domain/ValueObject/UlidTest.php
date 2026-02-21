<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\ValueObject;

use App\Shared\Domain\Exception\ValueObject\InvalidUlidException;
use App\Shared\Domain\ValueObject\Ulid;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UlidTest extends TestCase
{
    /**
     * @throws InvalidUlidException
     */
    public function testItCreatesValidUlid(): void
    {
        $ulid = '01KHVRCA0FCCYAQT1P88R317DD';
        $vo = Ulid::fromString($ulid);

        self::assertSame($ulid, $vo->value());
        self::assertSame($ulid, (string) $vo);
    }

    /**
     * @throws InvalidUlidException
     */
    public function testItTrimsInput(): void
    {
        $ulid = '01KHVRCA0FCCYAQT1P88R317DD';
        $vo = Ulid::fromString('  '.$ulid.'  ');

        self::assertSame($ulid, $vo->value());
    }

    /**
     * @throws InvalidUlidException
     */
    public function testItProvidesEqualityCheck(): void
    {
        $vo1 = Ulid::fromString('01KHVRCA0FCCYAQT1P88R317DD');
        $vo2 = Ulid::fromString('01KHVRCA0FCCYAQT1P88R317DD');
        $vo3 = Ulid::fromString('01KHVRCA679BJ6PBXX5N3G6RR5');

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }

    #[DataProvider('invalidUlidProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidUlidException::class);
        Ulid::fromString($invalidValue);
    }

    public static function invalidUlidProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'only spaces' => ['   '];
        yield 'wrong format' => ['01952796-03f3-493a-867c-d6159f8a3290'];
        yield 'wrong length' => ['91KHVRCA0FCCYAQT1P88R317'];
    }
}
