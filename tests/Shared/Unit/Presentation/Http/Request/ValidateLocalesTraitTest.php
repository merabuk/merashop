<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Presentation\Http\Request;

use App\Shared\Domain\Enum\LocaleEnum;
use App\Shared\Presentation\Http\Request\ValidateLocalesTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class ValidateLocalesTraitTest extends TestCase
{
    private ExecutionContextInterface&MockObject $context;
    private ConstraintViolationBuilderInterface&MockObject $violationBuilder;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
    }

    public function testValidateLocalesDoesNothingWhenAllLocalesArePresent(): void
    {
        $trait = $this->createAnonymousClass();

        $trait->translations = array_fill_keys(LocaleEnum::getValues(), ['name' => 'test']);

        $this->context->expects(self::never())->method('buildViolation');

        $trait->validateLocales($this->context);
    }

    public function testItHandlesMissingLocales(): void
    {
        $trait = $this->createAnonymousClass();
        $trait->translations = ['en' => []];

        $this->context->expects(self::once())
            ->method('buildViolation')
            ->with('translations.missing')
            ->willReturn($this->violationBuilder);

        $this->violationBuilder->expects(self::once())
            ->method('setParameter')
            ->with('%locales%', 'uk')
            ->willReturnSelf();

        $this->violationBuilder->expects(self::once())
            ->method('atPath')
            ->with('translations')
            ->willReturnSelf();

        $this->violationBuilder->expects(self::once())->method('addViolation');

        $trait->validateLocales($this->context);
    }

    public function testItHandlesInvalidLocales(): void
    {
        $trait = $this->createAnonymousClass();
        $trait->translations = ['en' => [], 'uk' => [], 'fr' => []];

        $this->context->expects(self::once())
            ->method('buildViolation')
            ->with('translations.invalid')
            ->willReturn($this->violationBuilder);

        $this->violationBuilder->expects(self::once())
            ->method('setParameter')
            ->with('%locale%', 'fr')
            ->willReturnSelf();

        $this->violationBuilder->expects(self::once())
            ->method('atPath')
            ->with('translations[fr]')
            ->willReturnSelf();

        $this->violationBuilder->expects(self::once())->method('addViolation');

        $trait->validateLocales($this->context);
    }

    private function createAnonymousClass(): object
    {
        return new class {
            use ValidateLocalesTrait;

            public ?array $translations = null;

            protected function getTranslations(): array
            {
                return $this->translations ?? [];
            }

            protected function getRequestTranslationKey(): string
            {
                return 'translations';
            }

            protected function getMissingTranslationKey(): string
            {
                return 'translations.missing';
            }

            protected function getInvalidTranslationKey(): string
            {
                return 'translations.invalid';
            }
        };
    }
}
