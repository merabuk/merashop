<?php

declare(strict_types=1);

namespace App\Tests\Customer\Unit\Application\Command\CreateCustomerProfile;

use App\Customer\Application\Command\CreateCustomerProfile\CreateCustomerProfileCommand;
use App\Customer\Application\Command\CreateCustomerProfile\CreateCustomerProfileHandler;
use App\Customer\Domain\Entity\CustomerProfile;
use App\Customer\Domain\Repository\CustomerProfileWriteRepositoryInterface;
use App\Tests\Customer\Support\CustomerProfileMother;
use PHPUnit\Framework\TestCase;

final class CreateCustomerProfileHandlerTest extends TestCase
{
    private CustomerProfileWriteRepositoryInterface $writeRepository;

    public function setUp(): void
    {
        $this->writeRepository = $this->createMock(CustomerProfileWriteRepositoryInterface::class);
    }

    public function testItSuccessfullyCreateCustomerProfile(): void
    {
        $command = new CreateCustomerProfileCommand(userUlid: CustomerProfileMother::DEFAULT_USER_ULID);

        $this->writeRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(fn (CustomerProfile $customerProfile) => $customerProfile->getUserUlid()->value() === $command->userUlid));

        $this->createHandler()($command);
    }

    private function createHandler(): CreateCustomerProfileHandler
    {
        return new CreateCustomerProfileHandler(writeRepository: $this->writeRepository);
    }
}
