<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service\Attribute;

use App\Catalog\Application\Command\CreateAttribute\CreateAttributeCommand;
use App\Catalog\Application\Command\UpdateAttribute\UpdateAttributeCommand;
use App\Catalog\Domain\Entity\Attribute;

interface AttributeApplicationFactoryInterface
{
    public function createFromCommand(CreateAttributeCommand $command): Attribute;

    public function updateFromCommand(Attribute $attribute, UpdateAttributeCommand $command): void;
}
