<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Attribute;

use App\Catalog\Domain\Exception\Attribute\InvalidAttributeIdException;
use App\Catalog\Domain\ValueObject\Attribute\Id;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\IntegerIdTestTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class IdTest extends BaseUnitTest
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
        $this->expectException(InvalidAttributeIdException::class);
        Id::fromInt($invalidValue);
    }
}
