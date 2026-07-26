<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\ValueObject\Identity;

use App\Shared\Domain\Exception\ValueObject\InvalidUlidException;
use App\Shared\Domain\ValueObject\Identity\Ulid;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class UlidTest extends BaseUnitTest
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validUlidProvider')]
    public function testItCreatesValidUlid(string $ulid, string $expected): void
    {
        $vo = Ulid::fromString($ulid);

        self::assertSame($expected, $vo->value());
        self::assertSame($expected, (string) $vo);
    }

    public static function validUlidProvider(): iterable
    {
        yield 'valid ulid' => ['01KHVRCA0FCCYAQT1P88R317DD', '01KHVRCA0FCCYAQT1P88R317DD'];
        yield 'trimmed' => ['  01KHVRCA0FCCYAQT1P88R317DD  ', '01KHVRCA0FCCYAQT1P88R317DD'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: Ulid::class,
            value: '01KHVRCA0FCCYAQT1P88R317DD',
            anotherValue: '01KHVRCA679BJ6PBXX5N3G6RR5'
        );
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
