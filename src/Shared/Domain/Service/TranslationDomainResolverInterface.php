<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

interface TranslationDomainResolverInterface
{
    public function resolveIcuDomain(string $domain): string;
}
