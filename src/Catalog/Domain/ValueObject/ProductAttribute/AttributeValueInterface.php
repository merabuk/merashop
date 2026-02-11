<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductAttribute;

use Stringable;

interface AttributeValueInterface extends Stringable
{
    public function value(): mixed;
}
