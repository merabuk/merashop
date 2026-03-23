<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\ProductPrice;

use App\Catalog\Domain\ValueObject\ProductPrice\ValidTo;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\DateTimeValueObjectTrait;
use PHPUnit\Framework\TestCase;

final class ValidToTest extends TestCase
{
    use DateTimeValueObjectTrait;

    public function testItCreatesValidValidTo(): void
    {
        $this->assertCreatesValidDateTime(className: ValidTo::class);
    }

    public function testItCreatesValidValidToFromString(): void
    {
        $this->assertCreatesValidDateTimeFromString(className: ValidTo::class);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertDateTimeEquality(ValidTo::class);
    }

    public function testItProvidesEqualityCheckWithDateTime(): void
    {
        $this->assertDateTimeEqualityWithDateTime(ValidTo::class);
    }

    public function testItIsAfter(): void
    {
        $this->assertIsAfter(ValidTo::class);
    }

    public function testItIsBefore(): void
    {
        $this->assertIsBefore(ValidTo::class);
    }
}
