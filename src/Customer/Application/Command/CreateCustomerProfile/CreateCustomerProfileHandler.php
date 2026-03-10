<?php

declare(strict_types=1);

namespace App\Customer\Application\Command\CreateCustomerProfile;

use App\Customer\Application\Exception\CreateCustomerProfileException;
use App\Customer\Domain\Entity\CustomerProfile;
use App\Customer\Domain\Repository\CustomerProfileWriteRepositoryInterface;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\ValueObject\Identity\Ulid;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class CreateCustomerProfileHandler implements CommandHandlerInterface
{
    public function __construct(
        private CustomerProfileWriteRepositoryInterface $writeRepository,
    ) {
    }

    /**
     * @throws CreateCustomerProfileException
     */
    public function __invoke(CreateCustomerProfileCommand $command): void
    {
        try {
            $customerProfile = CustomerProfile::create(userUlid: Ulid::fromString($command->userUlid));

            $this->writeRepository->save($customerProfile);
        } catch (Throwable $e) {
            throw new CreateCustomerProfileException(message: 'Error while creating customer profile', previous: $e);
        }
    }
}
