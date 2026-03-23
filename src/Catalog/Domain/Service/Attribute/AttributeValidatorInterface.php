<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service\Attribute;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Exception\Attribute\AttributeAlreadyExistsException;
use App\Catalog\Domain\Exception\Attribute\AttributeTypeCanNotBeCahngedException;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Type;
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
     * @throws AttributeAlreadyExistsException
     * @throws AttributeTypeCanNotBeCahngedException
     * @throws ConcurrencyException
     */
    public function validateUpdate(
        Attribute $attribute,
        int $version,
        Code $newCode,
        Type $newType,
    ): void;
}
