<?php

declare(strict_types=1);

namespace App\Tests\Customer\Unit\Application\Command\UpdateCustomerProfile;

use App\Customer\Application\Command\UpdateCustomerProfile\UpdateCustomerProfileCommand;
use App\Customer\Application\Command\UpdateCustomerProfile\UpdateCustomerProfileHandler;
use App\Customer\Domain\Exception\CustomerProfile\CustomerProfileNotFoundException;
use App\Customer\Domain\Repository\CustomerProfileReadRepositoryInterface;
use App\Customer\Domain\Repository\CustomerProfileWriteRepositoryInterface;
use App\Tests\Customer\Support\CustomerProfileMother;
use App\Tests\Shared\BaseUnitTest;
use PHPUnit\Framework\MockObject\MockObject;

final class UpdateCustomerProfileHandlerTest extends BaseUnitTest
{
    private CustomerProfileReadRepositoryInterface&MockObject $readRepository;
    private CustomerProfileWriteRepositoryInterface&MockObject $writeRepository;

    public function setUp(): void
    {
        $this->readRepository = $this->createMock(CustomerProfileReadRepositoryInterface::class);
        $this->writeRepository = $this->createMock(CustomerProfileWriteRepositoryInterface::class);
    }

    public function testItSuccessfullyUpdateCustomerProfile(): void
    {
        $customerProfile = CustomerProfileMother::createWithData(
            firstName: 'Jane',
            lastName: 'Doe',
            phoneNumber: '+380998877666'
        );

        $command = new UpdateCustomerProfileCommand(
            userUlid: $customerProfile->getUserUlid()->value(),
            firstName: 'John',
            lastName: 'Snow',
            phoneNumber: '+380995544333'
        );

        $this->readRepository->expects(self::once())
            ->method('getByUlid')
            ->willReturn($customerProfile);
        $this->writeRepository->expects(self::once())
            ->method('save')
            ->with($customerProfile)
            ->willReturn($customerProfile);

        $this->createHandler()($command);

        self::assertSame($command->firstName, $customerProfile->getFirstName()->value());
        self::assertSame($command->lastName, $customerProfile->getLastName()->value());
        self::assertSame($command->phoneNumber, $customerProfile->getPhoneNumber()->value());
    }

    public function testThrowsExceptionWhenCustomerProfileNotFound(): void
    {
        $command = new UpdateCustomerProfileCommand(
            userUlid: CustomerProfileMother::DEFAULT_USER_ULID,
            firstName: 'John',
            lastName: 'Snow',
            phoneNumber: '+380995544333'
        );

        $this->readRepository->expects(self::once())
            ->method('getByUlid')
            ->willThrowException(new CustomerProfileNotFoundException());

        $this->expectException(CustomerProfileNotFoundException::class);

        $this->createHandler()($command);
    }

    private function createHandler(): UpdateCustomerProfileHandler
    {
        return new UpdateCustomerProfileHandler(
            readRepository: $this->readRepository,
            writeRepository: $this->writeRepository
        );
    }
}
