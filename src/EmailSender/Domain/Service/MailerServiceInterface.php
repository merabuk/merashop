<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Service;

use App\EmailSender\Domain\Entity\OutboxEmail;

interface MailerServiceInterface
{
    public function process(OutboxEmail $email): void;
}
