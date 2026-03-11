<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\Service\Validation;

use App\Shared\Domain\Exception\Services\InvalidUlidException;
use App\Shared\Domain\Service\Validation\UlidValidator;
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
}
