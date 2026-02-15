<?php

declare(strict_types=1);

namespace App\EmailSender\Application\Service\ContentProvider;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('email_sender.email_content_provider')]
interface EmailContentProviderInterface
{
    public static function getDefaultIndexName(): string;

    /**
     * @param array<string, mixed> $params
     */
    public function getSubject(array $params): string;

    public function getTemplate(): string;
}
