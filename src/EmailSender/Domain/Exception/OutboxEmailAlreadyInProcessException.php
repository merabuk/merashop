<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Exception;

class OutboxEmailAlreadyInProcessException extends EmailSenderDomainException
{
}
