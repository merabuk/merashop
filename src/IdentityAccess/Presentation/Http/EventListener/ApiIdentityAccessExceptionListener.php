<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Http\EventListener;

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
use Throwable;

final class ApiIdentityAccessExceptionListener
{
    private const string OAUTH2_TOKEN_PATH = '/api/v1/identity-access/auth/token';

    public function __construct()
    {
    }

    /**
     * The priority of this listener should be higher than the default one (10) to prevent
     * the default exception handler from handling the exception.
     */
    #[AsEventListener(event: KernelEvents::EXCEPTION, priority: 20)]
    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $exception = $event->getThrowable();

        if (self::OAUTH2_TOKEN_PATH !== $request->getPathInfo()) {
            return;
        }

        $response = $this->handleIdentityException($exception);

        if ($response) {
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }

    private function handleIdentityException(Throwable $exception): ?JsonResponse
    {
        $previousException = $exception->getPrevious();

        if (
            $exception instanceof HttpExceptionInterface
            && $previousException instanceof ValidationFailedException
        ) {
            $violations = $previousException->getViolations();

            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage());
            }

            return $this->baseOAuth2Response(
                errorCode: OAuth2Error::INVALID_REQUEST,
                errorMessage: implode(', ', $errors),
                statusCode: Response::HTTP_BAD_REQUEST
            );
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

        return $this->baseOAuth2Response($errorCode, OAuth2Error::getDescription($errorCode), $statusCode);
    }

    /**
     * Different response format because OAuth2 requires another response format (RFC 6749, 5.2).
     */
    private function baseOAuth2Response(string $errorCode, string $errorMessage, int $statusCode): JsonResponse
    {
        return new JsonResponse([
            'error' => $errorCode,
            'error_description' => $errorMessage,
        ], $statusCode);
    }
}
