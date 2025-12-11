<?php

namespace App\Users\Domain\Exception;

use App\Shared\Domain\Exception\ServerException;

abstract class UserDomainException extends ServerException implements ThrowableUsersException
{
}
