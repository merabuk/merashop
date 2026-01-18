<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\EventListener;

use App\IdentityAccess\Application\Exceptions\InvalidClientException;
use App\IdentityAccess\Application\Exceptions\InvalidCredentialsException;
use App\IdentityAccess\Application\Exceptions\InvalidRefreshTokenException;
use App\IdentityAccess\Application\Exceptions\UnsupportedGrantTypeException;
use App\IdentityAccess\Domain\Exception\IdentityAccessDomainException;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class IdentityExceptionListener
{
    private const string AUTH_PATH = '/api/v1/identity-access/auth/token';

    public function __construct(private ServerRequestInterface $serverRequest)
    {
    }

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
        if ($exception instanceof InvalidCredentialsException) {
            $oauthEx = OAuthServerException::invalidCredentials();

            return $this->responseWithOAuthError($oauthEx);
        }

        if ($exception instanceof InvalidClientException) {
            $oauthEx = OAuthServerException::invalidClient($this->serverRequest);

            return $this->responseWithOAuthError($oauthEx);
        }

        if ($exception instanceof InvalidRefreshTokenException) {
            $oauthEx = OAuthServerException::invalidRefreshToken();

            return $this->responseWithOAuthError($oauthEx);
        }

        if ($exception instanceof UnsupportedGrantTypeException) {
            $oauthEx = OAuthServerException::unsupportedGrantType();

            return $this->responseWithOAuthError($oauthEx);
        }

        if ($exception instanceof IdentityAccessDomainException) {
            $oauthEx = OAuthServerException::invalidCredentials();

            return $this->responseWithOAuthError($oauthEx);
        }

        return null;
    }

    private function responseWithOAuthError(OAuthServerException $oauthEx): JsonResponse
    {
        return new JsonResponse([
            'error' => $oauthEx->getPayload()['error'],
            'error_description' => $oauthEx->getPayload()['error_description'],
        ], $oauthEx->getHttpStatusCode());
    }
}
