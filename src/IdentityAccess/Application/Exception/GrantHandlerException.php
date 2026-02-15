<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Exception;

use App\IdentityAccess\Domain\Exception\IdentityAccessDomainException;

abstract class GrantHandlerException extends IdentityAccessDomainException
{
}
