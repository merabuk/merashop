<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\EventListener;

use App\Shared\Domain\Enum\LocaleEnum;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 30)]
final class LocaleListener
{
    private const string COOKIE_NAME = '_locale';

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if ($request->attributes->has('_locale')) {
            return;
        }

        $cookieLocale = $request->cookies->get(self::COOKIE_NAME);
        if ($cookieLocale && LocaleEnum::tryFrom($cookieLocale)) {
            $request->setLocale($cookieLocale);

            return;
        }

        $preferred = $request->getPreferredLanguage(LocaleEnum::getValues());
        if ($preferred) {
            $request->setLocale($preferred);

            return;
        }

        $request->setLocale(LocaleEnum::default()->value);
    }
}
