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
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->attributes->get('_locale')) {
            return;
        }

        $preferred = $request->getPreferredLanguage(LocaleEnum::getValues());

        if ($preferred) {
            $request->setLocale($preferred);
        // \Locale::setDefault($preferred);
        } else {
            $request->setLocale(LocaleEnum::default()->value);
        }
    }
}
