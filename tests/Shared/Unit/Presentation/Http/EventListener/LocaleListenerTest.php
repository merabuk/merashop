<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Presentation\Http\EventListener;

use App\Shared\Domain\Enum\LocaleEnum;
use App\Shared\Presentation\Http\EventListener\LocaleListener;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Support\Traits\AppListenerTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;

final class LocaleListenerTest extends BaseUnitTest
{
    use AppListenerTrait;

    #[DataProvider('localeProvider')]
    public function testItSetsCorrectLocale(
        ?string $acceptLanguage,
        string $expectedLocale,
    ): void {
        $listener = new LocaleListener();
        $request = new Request();
        if ($acceptLanguage) {
            $request->headers->set('Accept-Language', $acceptLanguage);
        }

        $listener->onKernelRequest($this->makeRequestEvent(request: $request));

        self::assertSame($expectedLocale, $request->getLocale());
    }

    public static function localeProvider(): iterable
    {
        yield 'explicit supported locale' => [LocaleEnum::Uk->value, LocaleEnum::Uk->value];
        yield 'unsupported locale falls back to default' => ['ru-RU', LocaleEnum::default()->value];
        yield 'empty header falls back to default' => [null, LocaleEnum::default()->value];
        yield 'complex header with priority' => ['fr-CH, fr;q=0.9, en;q=0.8, *;q=0.5', LocaleEnum::En->value];
    }

    public function testItDoesNotOverwriteLocaleFromRoute(): void
    {
        $listener = new LocaleListener();
        $request = new Request();

        $request->attributes->set('_locale', LocaleEnum::Uk->value);
        $request->setLocale(LocaleEnum::Uk->value);

        $request->headers->set('Accept-Language', LocaleEnum::En->value);

        $listener->onKernelRequest($this->makeRequestEvent(request: $request));

        self::assertSame(LocaleEnum::Uk->value, $request->getLocale());
    }
}
