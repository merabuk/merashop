<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Exception;

use App\Shared\Domain\Exception\ThrowableValueObjectException;

abstract class InvalidEmailSenderValueObjectException extends EmailSenderDomainException implements ThrowableValueObjectException
{
}
