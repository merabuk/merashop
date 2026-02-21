<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\Service;

use App\Shared\Domain\Exception\InvalidUlidException;
use App\Shared\Domain\Exception\InvalidUuidException;
use App\Shared\Domain\Service\UlidValidator;
use App\Shared\Domain\Service\UuidValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UlidValidatorTest extends TestCase
{
    #[DataProvider('validUlidProvider')]
    public function testUlidValidator(string $ulid): void
    {
        self::assertSame($ulid, UlidValidator::validate($ulid));
    }

    public static function validUlidProvider(): iterable
    {
        yield ['01ARZ3NDEKTSV4RRFFQ6KHNQZY'];
        yield ['01H7B6H4X8D5G6H7J9K0M2N4P6'];
    }

    public function testUlidValidatorThrowsException(): void
    {
        $this->expectException(InvalidUlidException::class);
        UlidValidator::validate('not-a-ulid');
    }

    #[DataProvider('validUuidV7Provider')]
    public function testUuidV7Validator(string $uuid): void
    {
        self::assertSame($uuid, UuidValidator::validateV7($uuid));
    }

    public static function validUuidV7Provider(): iterable
    {
        yield ['018c292f-1e3c-7000-8000-000000000000'];
        yield ['01952796-03f3-793a-867c-d6159f8a329f'];
    }

    public function testUuidValidatorThrowsExceptionOnInvalidVersion(): void
    {
        $this->expectException(InvalidUuidException::class);
        UuidValidator::validateV7('550e8400-e29b-41d4-a716-446655440000');
    }
}
