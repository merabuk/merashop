<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception;

use App\Shared\Domain\Exception\Markers\ValueObjectExceptionInterface;

abstract class InvalidCatalogValueObjectException extends CatalogDomainException implements ValueObjectExceptionInterface
{
}
