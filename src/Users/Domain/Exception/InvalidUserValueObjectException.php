<?php

declare(strict_types=1);

namespace App\Users\Domain\Exception;

use App\Shared\Domain\Exception\ThrowableValueObjectException;

abstract class InvalidUserValueObjectException extends UserDomainException implements ThrowableValueObjectException
{
}
