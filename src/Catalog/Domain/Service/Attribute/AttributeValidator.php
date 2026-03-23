<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service\Attribute;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Exception\Attribute\AttributeAlreadyExistsException;
use App\Catalog\Domain\Exception\Attribute\AttributeTypeCanNotBeCahngedException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Type;
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
     * @throws AttributeAlreadyExistsException
     * @throws AttributeTypeCanNotBeCahngedException
     * @throws ConcurrencyException
     */
    public function validateUpdate(
        Attribute $attribute,
        int $version,
        Code $newCode,
        Type $newType,
    ): void {
        if ($this->checkTypeCanNotBeChanged($attribute->getType(), $newType)) {
            throw new AttributeTypeCanNotBeCahngedException();
        }

        if ($attribute->getVersion()->value() !== $version) {
            throw new ConcurrencyException();
        }

        if (!$attribute->getCode()->equals($newCode) && $this->readRepository->existsByCode($newCode)) {
            throw AttributeAlreadyExistsException::becauseAttributeCodeAlreadyExists($newCode->value());
        }
    }

    private function checkTypeCanNotBeChanged(Type $currentType, Type $newType): bool
    {
        // TODO: add change between string and text in future

        return false === $currentType->equals($newType);
    }
}
