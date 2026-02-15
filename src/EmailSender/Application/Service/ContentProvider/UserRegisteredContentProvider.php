<?php

declare(strict_types=1);

namespace App\EmailSender\Application\Service\ContentProvider;

use App\Shared\Domain\Enum\SharedEventNameEnum;
use App\Shared\Domain\Service\TranslationDomainResolverInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class UserRegisteredContentProvider implements EmailContentProviderInterface
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
            id: 'user_registered.subject',
            parameters: $params,
            domain: $this->translationDomainResolver->resolveIcuDomain('emails_public'),
        );
    }

    public function getTemplate(): string
    {
        return '@EmailSender/emails/public/user_registered.html.twig';
    }

    private static function getEventRoutingKey(): SharedEventNameEnum
    {
        return SharedEventNameEnum::UserRegistered;
    }
}
