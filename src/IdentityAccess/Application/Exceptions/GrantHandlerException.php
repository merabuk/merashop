<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Exceptions;

use App\IdentityAccess\Domain\Exception\IdentityAccessDomainException;

abstract class GrantHandlerException extends IdentityAccessDomainException
{
}
