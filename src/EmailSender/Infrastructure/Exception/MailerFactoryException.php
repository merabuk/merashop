<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Exception;

use App\EmailSender\Domain\Exception\EmailSenderDomainException;

class MailerFactoryException extends EmailSenderDomainException
{
}
