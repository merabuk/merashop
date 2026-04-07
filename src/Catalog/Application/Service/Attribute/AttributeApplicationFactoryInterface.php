<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service\Attribute;

use App\Catalog\Application\Command\CreateAttribute\CreateAttributeCommand;
use App\Catalog\Application\Command\UpdateAttribute\UpdateAttributeCommand;
use App\Catalog\Application\DTO\Attribute\AttributeOptionData;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\ValueObject\AttributeOption\Ulid as AttributeOptionUlid;

interface AttributeApplicationFactoryInterface
{
    /**
     * @param AttributeOptionData[] $optionsData
     *
     * @return AttributeOptionUlid[]
     */
    public function mapAttributeOptionUlids(array $optionsData, bool $associative = false): array;

    public function createFromCommand(CreateAttributeCommand $command): Attribute;

    public function updateFromCommand(Attribute $attribute, UpdateAttributeCommand $command): void;
}
