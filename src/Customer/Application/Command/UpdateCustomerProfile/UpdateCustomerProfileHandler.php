<?php

declare(strict_types=1);

namespace App\Customer\Application\Command\UpdateCustomerProfile;

use App\Customer\Application\Exception\UpdateCustomerProfileException;
use App\Customer\Domain\Exception\CustomerProfile\CustomerProfileNotFoundException;
use App\Customer\Domain\Repository\CustomerProfileReadRepositoryInterface;
use App\Customer\Domain\Repository\CustomerProfileWriteRepositoryInterface;
use App\Customer\Domain\ValueObject\CustomerProfile\FirstName;
use App\Customer\Domain\ValueObject\CustomerProfile\LastName;
use App\Customer\Domain\ValueObject\CustomerProfile\PhoneNumber;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\ValueObject\Identity\Ulid;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class UpdateCustomerProfileHandler implements CommandHandlerInterface
{
    public function __construct(
        private CustomerProfileReadRepositoryInterface $readRepository,
        private CustomerProfileWriteRepositoryInterface $writeRepository,
    ) {
    }

    /**
     * @throws CustomerProfileNotFoundException
     * @throws UpdateCustomerProfileException
     */
    public function __invoke(UpdateCustomerProfileCommand $command): void
    {
        try {
            $customerProfile = $this->readRepository->getByUlid(Ulid::fromString($command->userUlid));

            $customerProfile->updatePersonalData(
                firstName: FirstName::fromString($command->firstName),
                lastName: LastName::fromString($command->lastName),
                phoneNumber: PhoneNumber::fromString($command->phoneNumber)
            );

            $this->writeRepository->save($customerProfile);
        } catch (CustomerProfileNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new UpdateCustomerProfileException(message: 'Error while updating customer profile', previous: $e);
        }
    }
}
