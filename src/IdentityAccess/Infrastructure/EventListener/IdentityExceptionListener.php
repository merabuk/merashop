<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\EventListener;

use App\IdentityAccess\Application\Exceptions\InvalidClientException;
use App\IdentityAccess\Application\Exceptions\InvalidCredentialsException;
use App\IdentityAccess\Application\Exceptions\InvalidRefreshTokenException;
use App\IdentityAccess\Application\Exceptions\UnsupportedGrantTypeException;
use App\IdentityAccess\Domain\Exception\IdentityAccessDomainException;
use App\IdentityAccess\Infrastructure\Security\OAuth2Error;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class IdentityExceptionListener
{
    private const string AUTH_PATH = '/api/v1/identity-access/auth/token';

    public function __construct()
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
        if (
            $exception instanceof HttpExceptionInterface
            && $exception->getPrevious() instanceof ValidationFailedException
        ) {
            /** @var ValidationFailedException $validationException */
            $validationException = $exception->getPrevious();
            $violations = $validationException->getViolations();

            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage());
            }

            return new JsonResponse([
                'error' => OAuth2Error::INVALID_REQUEST,
                'error_description' => implode(', ', $errors),
            ], Response::HTTP_BAD_REQUEST);
        }

        $errorCode = match (true) {
            $exception instanceof InvalidClientException => OAuth2Error::INVALID_CLIENT,
            $exception instanceof InvalidCredentialsException,
            $exception instanceof InvalidRefreshTokenException => OAuth2Error::INVALID_GRANT,
            $exception instanceof UnsupportedGrantTypeException => OAuth2Error::UNSUPPORTED_GRANT_TYPE,
            $exception instanceof IdentityAccessDomainException => OAuth2Error::SERVER_ERROR,

            default => null,
        };

        if (null === $errorCode) {
            return null;
        }

        $statusCode = match (true) {
            OAuth2Error::INVALID_CLIENT === $errorCode => Response::HTTP_UNAUTHORIZED,
            default => Response::HTTP_BAD_REQUEST,
        };

        return new JsonResponse([
            'error' => $errorCode,
            'error_description' => OAuth2Error::getDescription($errorCode),
        ], $statusCode);
    }
}
