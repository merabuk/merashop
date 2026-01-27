<?php

namespace App\Shared\Domain\Exception;

abstract class ServerException extends \Exception implements \Throwable
{
    abstract public function getErrorCode(): string;
}
