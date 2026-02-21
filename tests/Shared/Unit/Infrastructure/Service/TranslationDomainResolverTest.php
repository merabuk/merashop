<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Infrastructure\Service;

use App\Shared\Infrastructure\Service\TranslationDomainResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\MessageCatalogueInterface;

final class TranslationDomainResolverTest extends TestCase
{
    public function testItResolvesIcuDomain(): void
    {
        $resolver = new TranslationDomainResolver();

        $domain = 'messages';
        $icuSuffix = MessageCatalogueInterface::INTL_DOMAIN_SUFFIX;

        self::assertSame($domain.$icuSuffix, $resolver->resolveIcuDomain(domain: $domain));
    }
}
