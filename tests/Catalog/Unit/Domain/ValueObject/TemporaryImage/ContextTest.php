<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\TemporaryImage;

use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;
use App\Catalog\Domain\Exception\TemporaryImage\InvalidTemporaryImageContextException;
use App\Catalog\Domain\ValueObject\TemporaryImage\Context;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContextTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('contextEnumProvider')]
    public function testItCreatesValidContextFromEnum(ContextEnum $enum): void
    {
        $vo = Context::fromEnum($enum);

        self::assertSame($enum, $vo->value());
        self::assertSame($enum->value, (string) $vo);
    }

    #[DataProvider('contextEnumProvider')]
    public function testItCreatesValidContextFromString(ContextEnum $enum): void
    {
        $vo = Context::fromString($enum->value);

        self::assertSame($enum, $vo->value());
        self::assertSame($enum->value, (string) $vo);
    }

    public static function contextEnumProvider(): iterable
    {
        foreach (ContextEnum::cases() as $enum) {
            yield $enum->name => [$enum];
        }
    }

    public function testItTrimsInput(): void
    {
        $context = ContextEnum::ProductMain;
        $vo = Context::fromString('  '.$context->value.'  ');
        self::assertSame($context, $vo->value());
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertEnumVOProvidesEqualityCheck(
            className: Context::class,
            enum: ContextEnum::ProductMain,
            anotherEnum: ContextEnum::CategoryIcon
        );
    }

    #[DataProvider('invalidContextProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidTemporaryImageContextException::class);
        Context::fromString($invalidValue);
    }

    public static function invalidContextProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['   '];
        yield 'wrong case' => ['PRODUCT_MAIN'];
        yield 'random string' => ['not-a-context'];
    }
}
