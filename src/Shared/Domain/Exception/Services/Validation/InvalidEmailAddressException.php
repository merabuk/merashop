<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\Services\Validation;

use App\Shared\Domain\Exception\InvalidArgumentException;

abstract class InvalidEmailAddressException extends InvalidArgumentException
{
}
