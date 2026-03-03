<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductAttribute;

use App\Shared\Domain\ValueObject\EquatableInterface;
use Stringable;

interface AttributeValueInterface extends EquatableInterface, Stringable
{
    public function value(): mixed;
}
