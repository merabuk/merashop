<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Product;

use App\Catalog\Domain\Exception\Product\InvalidProductSkuException;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class SkuTest extends BaseUnitTest
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validSkuProvider')]
    public function testItCreatesValidSku(string $sku, string $expected): void
    {
        $vo = Sku::fromString($sku);

        self::assertSame($expected, $vo->value());
        self::assertSame($expected, (string) $vo);
    }

    public static function validSkuProvider(): iterable
    {
        yield 'simple' => ['SKU-123-T', 'SKU-123-T'];
        yield 'trimmed' => ['  SKU-123-T  ', 'SKU-123-T'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: Sku::class,
            value: 'VALID-SKU',
            anotherValue: 'DIFFERENT-SKU'
        );
    }

    #[DataProvider('invalidSkuProvider')]
    public function testThrowsExceptionForInvalidSlug(string $invalidValue): void
    {
        $this->expectException(InvalidProductSkuException::class);
        Sku::fromString($invalidValue);
    }

    public static function invalidSkuProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['   '];
        yield 'toos hort' => [str_repeat('X', Sku::MIN_LENGTH - 1)];
        yield 'too long' => [str_repeat('X', Sku::MAX_LENGTH + 1)];
        yield 'wrong case' => ['sku-123-t'];
        yield 'leading digit' => ['123-SKU-T'];
        yield 'leading dash' => ['-SKU-123-T'];
        yield 'trailing dash' => ['SKU-123-T-'];
        yield 'multiple dashes' => ['SKU--123-T'];
        yield 'non-alphanumeric characters' => ['SKU-123-T-!'];
    }
}
