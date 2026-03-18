<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateAttribute;

use App\Catalog\Application\Exception\Attribute\UpdateAttributeException;
use App\Catalog\Domain\Exception\Attribute\AttributeAlreadyExistsException;
use App\Catalog\Domain\Exception\Attribute\AttributeNotFoundException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\Repository\AttributeWriteRepositoryInterface;
use App\Catalog\Domain\Service\Attribute\AttributeValidatorInterface;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Id;
use App\Catalog\Domain\ValueObject\Attribute\Translations;
use App\Catalog\Domain\ValueObject\Attribute\Type;
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
        private AttributeWriteRepositoryInterface $writeRepository,
    ) {
    }

    /**
     * @throws AttributeAlreadyExistsException
     * @throws AttributeNotFoundException
     * @throws UpdateAttributeException
     * @throws ConcurrencyException
     */
    public function __invoke(UpdateAttributeCommand $command): void
    {
        try {
            $attribute = $this->readRepository->getById(Id::fromInt($command->id));
            $newCode = Code::fromString($command->code);

            $this->attributeValidator->validateUpdate($attribute, $command->version, $newCode);

            $attribute->update(
                code: $newCode,
                type: Type::fromString($command->type),
                translations: Translations::fromArray($command->translations),
                updatedBy: AdminUlid::fromString($command->adminUlid),
            );

            $this->writeRepository->save($attribute);
        } catch (AttributeAlreadyExistsException|AttributeNotFoundException|ConcurrencyException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new UpdateAttributeException(message: 'Error during updating attribute', previous: $e);
        }
    }
}
