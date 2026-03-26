<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service\Attribute;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Exception\Attribute\AttributeAlreadyExistsException;
use App\Catalog\Domain\Exception\Attribute\AttributeTypeCanNotBeChangedException;
use App\Catalog\Domain\Exception\AttributeOption\AttributeOptionNotFoundException;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\AttributeOption\Ulid as AttributeOptionUlid;
use App\Shared\Domain\Exception\Entity\ConcurrencyException;

interface AttributeValidatorInterface
{
    /**
     * @throws AttributeAlreadyExistsException
     */
    public function validateCreation(
        Code $code,
    ): void;

    /**
     * @param AttributeOptionUlid[] $optionsUlids
     *
     * @throws AttributeAlreadyExistsException
     * @throws AttributeOptionNotFoundException
     * @throws AttributeTypeCanNotBeChangedException
     * @throws ConcurrencyException
     */
    public function validateUpdate(
        Attribute $attribute,
        int $version,
        Code $newCode,
        Type $newType,
        array $optionsUlids,
    ): void;
}
