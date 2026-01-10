<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Exception;

use App\Shared\Domain\Exception\ServerException;

abstract class IdentityAccessDomainException extends ServerException implements ThrowableIdentityAccessException
{
}
