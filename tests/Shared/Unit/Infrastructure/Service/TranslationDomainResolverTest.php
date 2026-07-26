<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Infrastructure\Service;

use App\Shared\Infrastructure\Service\TranslationDomainResolver;
use App\Tests\Shared\BaseUnitTest;
use Symfony\Component\Translation\MessageCatalogueInterface;

final class TranslationDomainResolverTest extends BaseUnitTest
{
    public function testItResolvesIcuDomain(): void
    {
        $resolver = new TranslationDomainResolver();

        $domain = 'messages';
        $icuSuffix = MessageCatalogueInterface::INTL_DOMAIN_SUFFIX;

        self::assertSame($domain.$icuSuffix, $resolver->resolveIcuDomain(domain: $domain));
    }
}
