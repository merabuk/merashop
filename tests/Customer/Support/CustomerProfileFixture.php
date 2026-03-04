<?php

declare(strict_types=1);

namespace App\Tests\Customer\Support;

use App\Customer\Domain\Entity\CustomerProfile;
use App\Customer\Domain\Repository\CustomerProfileWriteRepositoryInterface;

final readonly class CustomerProfileFixture
{
    public function __construct(
        private CustomerProfileMother $customerProfileMother,
        private CustomerProfileWriteRepositoryInterface $repository,
    ) {
    }

    public function create(
        ?string $userUlid = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $phoneNumber = null,
    ): CustomerProfile {
        $customerProfile = $this->customerProfileMother->create(
            userUlid: $userUlid,
            firstName: $firstName,
            lastName: $lastName,
            phoneNumber: $phoneNumber
        );

        return $this->repository->save($customerProfile);
    }

    /**
     * @return CustomerProfile[]
     */
    public function createMany(int $count): array
    {
        $items = [];
        for ($i = 0; $i < $count; ++$i) {
            $items[] = $this->create();
        }

        return $items;
    }
}
