<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Service;

use App\Shared\Domain\Service\TranslationDomainResolverInterface;
use Symfony\Component\Translation\MessageCatalogueInterface;

final class TranslationDomainResolver implements TranslationDomainResolverInterface
{
    public function resolveIcuDomain(string $domain): string
    {
        return $domain.MessageCatalogueInterface::INTL_DOMAIN_SUFFIX;
    }
}
