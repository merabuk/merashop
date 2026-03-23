<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductAttributeValue\Value;

use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use Stringable;

interface AttributeValueInterface extends EquatableInterface, Stringable
{
}
