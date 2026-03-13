<?php

declare(strict_types=1);

use App\EmailSender\Domain\Enum\ErrorCodeEnum;

return [
    ErrorCodeEnum::EmailSenderDomainError->value => 'Something went wrong in the email sender domain. Please try again later',
];
