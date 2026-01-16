<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\EventListener;

use App\IdentityAccess\Domain\Exception\IdentityAccessDomainException;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class IdentityExceptionListener
{
    private const string AUTH_PATH = '/api/v1/identity-access/auth/token';

    #[AsEventListener(event: KernelEvents::EXCEPTION, priority: 20)]
    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $exception = $event->getThrowable();

        if (self::AUTH_PATH !== $request->getPathInfo()) {
            return;
        }

        $response = $this->handleIdentityException($exception);

        if ($response) {
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }

    private function handleIdentityException(\Throwable $exception): ?JsonResponse
    {
        if ($exception instanceof IdentityAccessDomainException) {
            $oauthEx = OAuthServerException::invalidCredentials();

            return new JsonResponse([
                'error' => $oauthEx->getPayload()['error'],
                'error_description' => $oauthEx->getPayload()['message'],
            ], $oauthEx->getHttpStatusCode());
        }

        return null;
    }
}
