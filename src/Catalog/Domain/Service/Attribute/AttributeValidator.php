<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service\Attribute;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Exception\Attribute\AttributeAlreadyExistsException;
use App\Catalog\Domain\Exception\Attribute\AttributeTypeCanNotBeChangedException;
use App\Catalog\Domain\Exception\AttributeOption\AttributeOptionNotFoundException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\AttributeOption\Ulid as AttributeOptionUlid;
use App\Shared\Domain\Exception\Entity\ConcurrencyException;

final readonly class AttributeValidator implements AttributeValidatorInterface
{
    public function __construct(
        private AttributeReadRepositoryInterface $readRepository,
    ) {
    }

    /**
     * @throws AttributeAlreadyExistsException
     */
    public function validateCreation(Code $code): void
    {
        if ($this->readRepository->existsByCode($code)) {
            throw AttributeAlreadyExistsException::becauseAttributeCodeAlreadyExists($code->value());
        }
    }

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
    ): void {
        if (false === $attribute->getType()->allowChange($newType)) {
            throw new AttributeTypeCanNotBeChangedException();
        }

        if ($attribute->getVersion()->value() !== $version) {
            throw new ConcurrencyException();
        }

        if (!$attribute->getCode()->equals($newCode) && $this->readRepository->existsByCode($newCode)) {
            throw AttributeAlreadyExistsException::becauseAttributeCodeAlreadyExists($newCode->value());
        }

        $currentOptions = $attribute->getOptions();
        foreach ($optionsUlids as $providedUlid) {
            if (!$currentOptions->getByUlid($providedUlid)) {
                throw AttributeOptionNotFoundException::withUlid($providedUlid->value());
            }
        }
    }
}
