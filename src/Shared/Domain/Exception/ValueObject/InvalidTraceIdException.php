<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\ValueObject;

use App\Shared\Domain\Exception\LogicException;
use App\Shared\Domain\Exception\ThrowableValueObjectException;

final class InvalidTraceIdException extends LogicException implements ThrowableValueObjectException
{
}
