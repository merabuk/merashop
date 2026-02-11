<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\CreateAttribute;

use App\Catalog\Application\Exception\Attribute\CreateAttributeException;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Repository\AttributeWriteRepositoryInterface;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Translations;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\Service\UlidGeneratorInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class CreateAttributeHandler implements CommandHandlerInterface
{
    public function __construct(
        private UlidGeneratorInterface $ulidGenerator,
        private AttributeWriteRepositoryInterface $writeRepository,
    ) {
    }

    /**
     * @throws CreateAttributeException
     */
    public function __invoke(CreateAttributeCommand $command): int
    {
        try {
            $ulid = $this->ulidGenerator->next();

            $attribute = Attribute::create(
                ulid: Ulid::fromString($ulid),
                code: Code::fromString($command->code),
                type: Type::fromString($command->type),
                translations: Translations::fromArray($command->translations),
            );

            $attribute = $this->writeRepository->save($attribute);

            return $attribute->getId()->value();
        } catch (InvalidCatalogValueObjectException|InvalidLocaleException $e) {
            throw new CreateAttributeException(message: 'Error during creating attribute', previous: $e);
        }
    }
}
