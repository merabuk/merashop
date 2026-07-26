<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\ProductAttributeValue\Value;

use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\DateValue;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\DateValueObjectTrait;

final class DateValueTest extends BaseUnitTest
{
    use DateValueObjectTrait;

    public function testItCreatesValidDateValue(): void
    {
        $this->assertCreatesValidDateTime(className: DateValue::class);
    }

    public function testItCreatesValidValidFromFromString(): void
    {
        $this->assertCreatesValidDateTimeFromString(className: DateValue::class);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertDateTimeEquality(className: DateValue::class);
    }

    public function testItProvidesEqualityCheckWithDateTime(): void
    {
        $this->assertDateTimeEqualityWithDateTime(DateValue::class);
    }

    public function testItIsAfter(): void
    {
        $this->assertIsAfter(DateValue::class);
    }

    public function testItIsBefore(): void
    {
        $this->assertIsBefore(DateValue::class);
    }
}
