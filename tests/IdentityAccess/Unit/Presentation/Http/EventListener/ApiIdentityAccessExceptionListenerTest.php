<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Presentation\Http\EventListener;

use App\IdentityAccess\Application\Exception\InvalidClientException;
use App\IdentityAccess\Infrastructure\Security\OAuth2\OAuth2Error;
use App\IdentityAccess\Presentation\Http\EventListener\ApiIdentityAccessExceptionListener;
use App\Shared\Domain\Service\TranslationDomainResolverInterface;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Support\Traits\AppListenerTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ApiIdentityAccessExceptionListenerTest extends BaseUnitTest
{
    use AppListenerTrait;

    private TranslatorInterface&MockObject $translator;
    private TranslationDomainResolverInterface&MockObject $domainResolver;
    private ApiIdentityAccessExceptionListener $listener;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->domainResolver = $this->createMock(TranslationDomainResolverInterface::class);

        $this->listener = $this->createListener();
    }

    public function testOnKernelExceptionHandlesOAuth2Path(): void
    {
        $exception = new InvalidClientException('Original message');
        $request = Request::create('/api/v1/identity-access/auth/token');

        $event = $this->makeExceptionEvent(request: $request, exception: $exception);

        $this->domainResolver->method('resolveIcuDomain')->willReturn('oauth2+intl-icu');
        $this->translator->method('trans')->willReturn('Translated error description');

        $this->listener->onKernelException($event);

        $response = $event->getResponse();
        self::assertNotNull($response);
        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        self::assertSame(OAuth2Error::INVALID_CLIENT, $data['error']);
        self::assertSame('Translated error description', $data['error_description']);
    }

    public function testItIgnoresOtherPaths(): void
    {
        $request = Request::create('/api/v1/other-module/data');

        $event = $this->makeExceptionEvent(request: $request);

        $this->listener->onKernelException($event);

        self::assertNull($event->getResponse());
        self::assertFalse($event->isPropagationStopped());
    }

    private function createListener(): ApiIdentityAccessExceptionListener
    {
        return new ApiIdentityAccessExceptionListener(
            translator: $this->translator,
            translationDomainResolver: $this->domainResolver
        );
    }
}
