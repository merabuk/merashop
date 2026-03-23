<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\ProductAttributeValue\Value;

use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\BooleanValue;
use App\Shared\Domain\ValueObject\BaseFlag;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\BooleanFlagValueObjectTrait;
use PHPUnit\Framework\TestCase;

final class BooleanValueTest extends TestCase
{
    use BooleanFlagValueObjectTrait;

    public function testItCreatesMainImageFlag(): void
    {
        $this->assertCreatesValidFlag(
            className: BooleanValue::class,
            value: true,
            expectedStringValue: BaseFlag::TRUE_STRING
        );
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertBooleanFlagEquality(className: BooleanValue::class);
    }
}
