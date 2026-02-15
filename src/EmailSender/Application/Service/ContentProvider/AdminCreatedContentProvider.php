<?php

declare(strict_types=1);

namespace App\EmailSender\Application\Service\ContentProvider;

use App\Shared\Domain\Enum\SharedEventNameEnum;
use App\Shared\Domain\Service\TranslationDomainResolverInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class AdminCreatedContentProvider implements EmailContentProviderInterface
{
    public function __construct(
        private TranslatorInterface $translator,
        private TranslationDomainResolverInterface $translationDomainResolver,
    ) {
    }

    public static function getDefaultIndexName(): string
    {
        return self::getEventRoutingKey()->value;
    }

    /**
     * @param array<string, mixed> $params
     */
    public function getSubject(array $params): string
    {
        return $this->translator->trans(
            id: 'admin_created.subject',
            parameters: $params,
            domain: $this->translationDomainResolver->resolveIcuDomain('emails_admin'),
        );
    }

    public function getTemplate(): string
    {
        return '@EmailSender/emails/admin/admin_created.html.twig';
    }

    private static function getEventRoutingKey(): SharedEventNameEnum
    {
        return SharedEventNameEnum::AdminCreated;
    }
}
