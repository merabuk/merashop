<?php

namespace App\Shared\Domain\Exception\Database;

use App\Shared\Domain\Exception\Markers\NotFoundExceptionInterface;

final class OneOfEntitiesNotFoundException extends DatabaseException implements NotFoundExceptionInterface
{
}
