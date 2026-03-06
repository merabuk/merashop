<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Category;

use App\Catalog\Domain\Exception\Category\InvalidCategoryIdException;
use App\Catalog\Domain\ValueObject\Category\Id;
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
        $this->expectException(InvalidCategoryIdException::class);
        Id::fromInt($invalidValue);
    }
}
