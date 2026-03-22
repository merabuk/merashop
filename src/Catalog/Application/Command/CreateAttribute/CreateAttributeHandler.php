<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\CreateAttribute;

use App\Catalog\Application\Exception\Attribute\CreateAttributeException;
use App\Catalog\Application\Service\Attribute\AttributeApplicationFactoryInterface;
use App\Catalog\Domain\Exception\Attribute\AttributeAlreadyExistsException;
use App\Catalog\Domain\Repository\AttributeWriteRepositoryInterface;
use App\Catalog\Domain\Service\Attribute\AttributeValidatorInterface;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class CreateAttributeHandler implements CommandHandlerInterface
{
    public function __construct(
        private AttributeValidatorInterface $attributeValidator,
        private AttributeApplicationFactoryInterface $attributeFactory,
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

            $this->attributeValidator->validateCreation($code);

            $attribute = $this->attributeFactory->createFromCommand($command);

            $attribute = $this->writeRepository->save($attribute);

            return $attribute->getId()->value();
        } catch (AttributeAlreadyExistsException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new CreateAttributeException(message: 'Error during creating attribute', previous: $e);
        }
    }
}
