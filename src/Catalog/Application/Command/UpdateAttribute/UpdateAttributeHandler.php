<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateAttribute;

use App\Catalog\Application\Exception\Attribute\CreateAttributeException;
use App\Catalog\Domain\Exception\Attribute\AttributeNotFoundException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\Repository\AttributeWriteRepositoryInterface;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Id;
use App\Catalog\Domain\ValueObject\Attribute\Translations;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class UpdateAttributeHandler implements CommandHandlerInterface
{
    public function __construct(
        private AttributeReadRepositoryInterface $readRepository,
        private AttributeWriteRepositoryInterface $writeRepository,
    ) {
    }

    /**
     * @throws AttributeNotFoundException
     * @throws CreateAttributeException
     */
    public function __invoke(UpdateAttributeCommand $command): int
    {
        try {
            $attribute = $this->readRepository->getById(Id::fromInt($command->id));

            $attribute->update(
                code: Code::fromString($command->code),
                type: Type::fromString($command->type),
                translations: Translations::fromArray($command->translations),
            );

            $attribute = $this->writeRepository->save($attribute);

            return $attribute->getId()->value();
        } catch (InvalidCatalogValueObjectException|InvalidLocaleException $e) {
            throw new CreateAttributeException(message: 'Error during updating attribute', previous: $e);
        }
    }
}
