<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\CreateAttribute;

use App\Catalog\Application\Exception\Attribute\CreateAttributeException;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Exception\Attribute\AttributeAlreadyExistsException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\Repository\AttributeWriteRepositoryInterface;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Translations;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\Service\UlidGeneratorInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class CreateAttributeHandler implements CommandHandlerInterface
{
    public function __construct(
        private AttributeReadRepositoryInterface $readRepository,
        private UlidGeneratorInterface $ulidGenerator,
        private AttributeWriteRepositoryInterface $writeRepository,
    ) {
    }

    /**
     * @throws AttributeAlreadyExistsException
     * @throws CreateAttributeException
     */
    public function __invoke(CreateAttributeCommand $command): int
    {
        try {
            $code = Code::fromString($command->code);

            if ($this->readRepository->existsByCode($code)) {
                throw AttributeAlreadyExistsException::becauseAttributeCodeAlreadyExists($code->value());
            }

            $ulid = $this->ulidGenerator->next();

            $attribute = Attribute::create(
                ulid: Ulid::fromString($ulid),
                code: $code,
                type: Type::fromString($command->type),
                translations: Translations::fromArray($command->translations),
                createdBy: AdminUlid::fromString($command->adminUlid),
            );

            $attribute = $this->writeRepository->save($attribute);

            return $attribute->getId()->value();
        } catch (AttributeAlreadyExistsException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new CreateAttributeException(message: 'Error during creating attribute', previous: $e);
        }
    }
}
