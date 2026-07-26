<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\ProductImage;

use App\Catalog\Domain\ValueObject\ProductImage\SortOrder;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;

final class SortOrderTest extends BaseUnitTest
{
    use ValueObjectEqualityCheckTrait;

    public function testItCreatesValidSortOrder(): void
    {
        $sortOrder = 1;
        $vo = SortOrder::fromInt($sortOrder);

        self::assertSame($sortOrder, $vo->value());
        self::assertSame((string) $sortOrder, (string) $vo);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertIntegerVOProvidesEqualityCheck(
            className: SortOrder::class,
            value: 1,
            anotherValue: 2
        );
    }

    public function testItGreaterThan(): void
    {
        $sortOrder = SortOrder::fromInt(1);
        $nextSortOrder = $sortOrder->next();

        self::assertTrue($nextSortOrder->greaterThan($sortOrder));
        self::assertTrue($nextSortOrder->greaterThan(1));
        self::assertFalse($sortOrder->greaterThan($nextSortOrder));
        self::assertFalse($sortOrder->greaterThan($sortOrder));
    }
}
