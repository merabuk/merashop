<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Mailer;

use App\EmailSender\Domain\Entity\OutboxEmail;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.email_driver')]
interface MailerInterface
{
    public function send(OutboxEmail $email): ?string;
}
