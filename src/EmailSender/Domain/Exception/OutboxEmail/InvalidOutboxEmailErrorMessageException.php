<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Exception\OutboxEmail;

use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;

final class InvalidOutboxEmailErrorMessageException extends InvalidEmailSenderValueObjectException
{
}
