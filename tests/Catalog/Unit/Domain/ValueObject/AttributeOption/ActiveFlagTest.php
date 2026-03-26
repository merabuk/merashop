<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\AttributeOption;

use App\Catalog\Domain\ValueObject\AttributeOption\ActiveFlag;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\BooleanFlagValueObjectTrait;
use PHPUnit\Framework\TestCase;

final class ActiveFlagTest extends TestCase
{
    use BooleanFlagValueObjectTrait;

    public function testItCreatesMainImageFlag(): void
    {
        $this->assertCreatesValidFlag(
            className: ActiveFlag::class,
            value: true,
        );
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertBooleanFlagEquality(className: ActiveFlag::class);
    }

    public function testItCreatesInactiveMainImageFlag(): void
    {
        $vo = ActiveFlag::inactive();

        self::assertSame(false, $vo->value());
    }
}
