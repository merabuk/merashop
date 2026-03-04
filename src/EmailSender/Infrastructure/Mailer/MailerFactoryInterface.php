<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Mailer;

use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use App\EmailSender\Infrastructure\Exception\MailerFactoryException;

interface MailerFactoryInterface
{
    /**
     * @throws MailerFactoryException
     */
    public function make(DriverEnum $driver): MailerInterface;
}
