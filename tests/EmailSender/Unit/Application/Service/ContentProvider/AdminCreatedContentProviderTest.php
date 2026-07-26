<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Application\Service\ContentProvider;

use App\EmailSender\Application\Service\ContentProvider\AdminCreatedContentProvider;
use App\Shared\Domain\Enum\SharedEventNameEnum;
use App\Shared\Domain\Service\TranslationDomainResolverInterface;
use App\Tests\Shared\BaseUnitTest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AdminCreatedContentProviderTest extends BaseUnitTest
{
    private TranslatorInterface&MockObject $translator;
    private TranslationDomainResolverInterface&MockObject $translationDomainResolver;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translationDomainResolver = $this->createMock(TranslationDomainResolverInterface::class);
    }

    public function testGetDefaultIndexName(): void
    {
        self::assertSame(SharedEventNameEnum::AdminCreated->value, $this->createContentProvider()::getDefaultIndexName());
    }

    private function createContentProvider(): AdminCreatedContentProvider
    {
        return new AdminCreatedContentProvider(
            translator: $this->translator,
            translationDomainResolver: $this->translationDomainResolver,
        );
    }
}
