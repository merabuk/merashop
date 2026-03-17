<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\ValueObject\Traits;

use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\ValueObject\AbstractTranslations;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @mixin TestCase
 */
trait TranslationsValueObjectTrait
{
    use ValueObjectEqualityCheckTrait;

    abstract protected static function getValidTranslations(): array;

    protected function assertReturnsNullForMissingLocale(string $className): void
    {
        $data = ['en' => self::getValidTranslations()['en']];

        $this->assertHasStaticMethod($className, 'fromArray');

        $vo = $className::fromArray($data);

        $this->assertVoExtendsAbstractTranslationsVo($vo);

        Assert::assertNull($vo->get('uk'));
    }

    protected function assertCanBeIterated(
        string $className,
        string $childClassName,
        string $locale = 'en',
        string $propertyName = 'name',
    ): void {
        $data = self::getValidTranslations();

        $this->assertHasStaticMethod($className, 'fromArray');

        $vo = $className::fromArray($data);

        $this->assertVoExtendsAbstractTranslationsVo($vo);

        $iterated = [];
        foreach ($vo as $l => $translation) {
            self::assertInstanceOf($childClassName, $translation);
            $iterated[$l] = $translation->{$propertyName};
        }

        Assert::assertCount(count($data), $iterated);
        Assert::assertSame($data[$locale][$propertyName], $iterated[$locale]);
    }

    protected function assertProvidesEqualityCheck(string $className): void
    {
        $data = self::getValidTranslations();

        $this->assertArrayVOProvidesEqualityCheck(
            className: $className,
            value: $data,
            shuffledValue: array_reverse($data, true),
            anotherValue: array_slice($data, 0, 1, true)
        );
    }

    protected function assertThrowsExceptionOnInvalidLocale(
        string $className,
        string $expectedException = InvalidLocaleException::class,
        string $translationKey = 'name',
    ): void {
        $this->assertHasStaticMethod($className, 'fromArray');

        $this->expectException($expectedException);
        $className::fromArray(['invalid' => [$translationKey => 'Test']]);
    }

    protected function assertVoExtendsAbstractTranslationsVo(object $vo): void
    {
        Assert::assertInstanceOf(AbstractTranslations::class, $vo);
    }
}
