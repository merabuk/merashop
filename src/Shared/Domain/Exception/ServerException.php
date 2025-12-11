<?php

namespace App\Shared\Domain\Exception;

abstract class ServerException extends \Exception implements \Throwable, ThrowableDomainException
{
}
