<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Helper\Traits;

use App\Shared\Domain\Service\TranslationDomainResolverInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

trait ResponseMessageTrait
{
    private function makeSuccessMessageForEntity(
        TranslatorInterface $translator,
        TranslationDomainResolverInterface $translationDomainResolver,
        string $messageKey,
        string $entityTranslationKey,
        string $moduleTranslationDomain,
    ): string {
        $domain = $translationDomainResolver->resolveIcuDomain($moduleTranslationDomain);

        return $translator->trans(
            id: $messageKey,
            parameters: [
                'entity' => $translator->trans(
                    id: $entityTranslationKey,
                    domain: $domain,
                ),
            ],
            domain: $domain
        );
    }
}
