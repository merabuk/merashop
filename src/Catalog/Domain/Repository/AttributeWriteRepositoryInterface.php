<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Entity\Attribute;

interface AttributeWriteRepositoryInterface
{
    public function save(Attribute $attribute): Attribute;

    public function delete(Attribute $attribute): void;
}
