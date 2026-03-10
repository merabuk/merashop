<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\TemporaryImage;

use App\Catalog\Domain\Exception\TemporaryImage\InvalidTemporaryImageIdException;
use App\Catalog\Domain\ValueObject\TemporaryImage\Id;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\IntegerIdTestTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IdTest extends TestCase
{
    use IntegerIdTestTrait;

    public function testItCreatesValidId(): void
    {
        $this->assertValidIntegerId(Id::class);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertIdEquality(Id::class);
    }

    #[DataProvider('invalidIdProvider')]
    public function testThrowsExceptionOnInvalidInput(int $invalidValue): void
    {
        $this->expectException(InvalidTemporaryImageIdException::class);
        Id::fromInt($invalidValue);
    }
}
