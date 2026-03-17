<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\ProductPrice;

use App\Catalog\Domain\ValueObject\ProductPrice\ValidFrom;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\DateTimeValueObjectTrait;
use PHPUnit\Framework\TestCase;

final class ValidFromTest extends TestCase
{
    use DateTimeValueObjectTrait;

    public function testItCreatesValidValidFrom(): void
    {
        $this->assertCreatesValidDateTime(className: ValidFrom::class);
    }

    public function testItCreatesValidValidFromFromString(): void
    {
        $this->assertCreatesValidDateTimeFromString(className: ValidFrom::class);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertDateTimeEquality(ValidFrom::class);
    }

    public function testItProvidesEqualityCheckWithDateTime(): void
    {
        $this->assertDateTimeEquality(ValidFrom::class);
    }

    public function testItIsAfter(): void
    {
        $this->assertIsAfter(ValidFrom::class);
    }

    public function testItIsBefore(): void
    {
        $this->assertIsBefore(ValidFrom::class);
    }
}
