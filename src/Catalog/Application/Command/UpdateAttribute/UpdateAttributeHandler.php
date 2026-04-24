<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateAttribute;

use App\Catalog\Application\Exception\Attribute\UpdateAttributeException;
use App\Catalog\Application\Service\Attribute\AttributeApplicationFactoryInterface;
use App\Catalog\Domain\Exception\Attribute\AttributeAlreadyExistsException;
use App\Catalog\Domain\Exception\Attribute\AttributeNotFoundException;
use App\Catalog\Domain\Exception\Attribute\AttributeTypeCanNotBeChangedException;
use App\Catalog\Domain\Exception\AttributeOption\AttributeOptionNotFoundException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\Repository\AttributeWriteRepositoryInterface;
use App\Catalog\Domain\Service\Attribute\AttributeValidatorInterface;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\Exception\Entity\ConcurrencyException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class UpdateAttributeHandler implements CommandHandlerInterface
{
    public function __construct(
        private AttributeReadRepositoryInterface $readRepository,
        private AttributeValidatorInterface $attributeValidator,
        private AttributeApplicationFactoryInterface $attributeFactory,
        private AttributeWriteRepositoryInterface $writeRepository,
    ) {
    }

    /**
     * @throws AttributeAlreadyExistsException
     * @throws AttributeNotFoundException
     * @throws AttributeOptionNotFoundException
     * @throws AttributeTypeCanNotBeChangedException
     * @throws UpdateAttributeException
     * @throws ConcurrencyException
     */
    public function __invoke(UpdateAttributeCommand $command): void
    {
        try {
            $attribute = $this->readRepository->getByUlid(Ulid::fromString($command->ulid));
            $newCode = Code::fromString($command->code);
            $newType = Type::fromString($command->type);
            $optionUlids = $this->attributeFactory->mapAttributeOptionUlids($command->options);

            $this->attributeValidator->validateUpdate(
                attribute: $attribute,
                version: $command->version,
                newCode: $newCode,
                newType: $newType,
                optionsUlids: $optionUlids,
            );

            $this->attributeFactory->updateFromCommand(attribute: $attribute, command: $command);

            $this->writeRepository->save($attribute);
        } catch (
            AttributeAlreadyExistsException
            |AttributeNotFoundException
            |AttributeOptionNotFoundException
            |AttributeTypeCanNotBeChangedException
            |ConcurrencyException $e
        ) {
            throw $e;
        } catch (Throwable $e) {
            throw new UpdateAttributeException(message: 'Error during updating attribute', previous: $e);
        }
    }
}
