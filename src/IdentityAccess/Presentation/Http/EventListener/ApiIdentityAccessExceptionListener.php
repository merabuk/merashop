<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Http\EventListener;

use App\IdentityAccess\Application\Exception\InvalidClientException;
use App\IdentityAccess\Application\Exception\InvalidCredentialsException;
use App\IdentityAccess\Application\Exception\InvalidRefreshTokenException;
use App\IdentityAccess\Application\Exception\UnsupportedGrantTypeException;
use App\IdentityAccess\Domain\Exception\IdentityAccessDomainException;
use App\IdentityAccess\Infrastructure\Security\OAuth2\OAuth2Error;
use App\Shared\Domain\Service\TranslationDomainResolverInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

final class ApiIdentityAccessExceptionListener
{
    private const string OAUTH2_PUBLIC_TOKEN_PATH = '/api/v1/identity-access/auth/token';
    private const string OAUTH2_ADMIN_TOKEN_PATH = '/admin/api/v1/identity-access/auth/token';
    private const string OAUTH2_INTERNAL_TOKEN_PATH = '/internal/api/v1/identity-access/auth/token';

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly TranslationDomainResolverInterface $translationDomainResolver,
    ) {
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

        if (!$this->canBeProcessed($request->getPathInfo())) {
            return;
        }

        $response = $this->handleIdentityException($exception);

        if ($response) {
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }

    private function canBeProcessed(string $path): bool
    {
        return self::OAUTH2_PUBLIC_TOKEN_PATH === $path
            || self::OAUTH2_ADMIN_TOKEN_PATH === $path
            || self::OAUTH2_INTERNAL_TOKEN_PATH === $path;
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
                statusCode: Response::HTTP_BAD_REQUEST,
                errorMessage: implode(', ', $errors)
            );
        }

        if ($exception instanceof HandlerFailedException && null !== $previousException) {
            $errorCode = $this->determineErrorCode($previousException);
        }

        $errorCode ??= $this->determineErrorCode($exception);

        if (null === $errorCode) {
            return null;
        }

        $statusCode = match (true) {
            OAuth2Error::INVALID_CLIENT === $errorCode => Response::HTTP_UNAUTHORIZED,
            default => Response::HTTP_BAD_REQUEST,
        };

        return $this->baseOAuth2Response(errorCode: $errorCode, statusCode: $statusCode);
    }

    private function determineErrorCode(Throwable $exception): ?string
    {
        return match (true) {
            $exception instanceof InvalidClientException => OAuth2Error::INVALID_CLIENT,
            $exception instanceof InvalidCredentialsException,
            $exception instanceof InvalidRefreshTokenException => OAuth2Error::INVALID_GRANT,
            $exception instanceof UnsupportedGrantTypeException => OAuth2Error::UNSUPPORTED_GRANT_TYPE,
            $exception instanceof IdentityAccessDomainException => OAuth2Error::SERVER_ERROR,
            default => null,
        };
    }

    /**
     * Different response format because OAuth2 requires another response format (RFC 6749, 5.2).
     */
    private function baseOAuth2Response(string $errorCode, int $statusCode, ?string $errorMessage = null): JsonResponse
    {
        return new JsonResponse([
            'error' => $errorCode,
            'error_description' => $errorMessage ?? $this->translator->trans(
                id: OAuth2Error::getDescriptionKey($errorCode),
                domain: $this->translationDomainResolver->resolveIcuDomain(OAuth2Error::getTranslationDomain()),
            ),
        ], $statusCode);
    }
}
