<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Exception\OutboxEmail;

use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectExceptionInterface;

final class InvalidOutboxEmailErrorMessageException extends InvalidEmailSenderValueObjectExceptionInterface
{
}
