<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Attribute;

use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CodeTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validCodeProvider')]
    public function testItCreatesValidCode(string $code, string $expected): void
    {
        $vo = Code::fromString($code);

        self::assertSame($expected, $vo->value());
        self::assertSame($expected, (string) $vo);
    }

    public static function validCodeProvider(): iterable
    {
        yield 'simple' => ['color', 'color'];
        yield 'trimmed' => ['  color  ', 'color'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: Code::class,
            value: 'color',
            anotherValue: 'different'
        );
    }
}
