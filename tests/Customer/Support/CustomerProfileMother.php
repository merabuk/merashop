<?php

declare(strict_types=1);

namespace App\Tests\Customer\Support;

use App\Customer\Domain\Entity\CustomerProfile;
use App\Customer\Domain\Factory\Contract\CustomerProfileFactoryInterface;
use App\Customer\Domain\ValueObject\CustomerProfile\FirstName;
use App\Customer\Domain\ValueObject\CustomerProfile\Id;
use App\Customer\Domain\ValueObject\CustomerProfile\LastName;
use App\Customer\Domain\ValueObject\CustomerProfile\PhoneNumber;
use App\Customer\Domain\ValueObject\CustomerProfile\Ulid;
use App\Shared\Domain\Service\Identity\UlidGeneratorInterface;
use Faker\Generator;

final readonly class CustomerProfileMother
{
    public const string DEFAULT_USER_ULID = '01ARZ3NDEKTSV4RRFFQ6KHNQZY';

    public function __construct(
        private CustomerProfileFactoryInterface $customerProfileFactory,
        private UlidGeneratorInterface $ulidGenerator,
        private Generator $faker,
    ) {
    }

    public static function createWithData(
        ?string $userUlid = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $phoneNumber = null,
        ?int $id = null,
    ): CustomerProfile {
        return new CustomerProfile(
            userUlid: Ulid::fromString($userUlid ?? self::DEFAULT_USER_ULID),
            firstName: $firstName ? FirstName::fromString($firstName) : null,
            lastName: $lastName ? LastName::fromString($lastName) : null,
            phoneNumber: $phoneNumber ? PhoneNumber::fromString($phoneNumber) : null,
            id: $id ? Id::fromInt($id) : null
        );
    }

    public function create(
        ?string $userUlid = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $phoneNumber = null,
    ): CustomerProfile {
        $gender = $this->faker->randomElement(['male', 'female']);

        return $this->customerProfileFactory->createForTest(
            userUlid: $userUlid ?? $this->ulidGenerator->next(),
            firstName: $firstName ?? $this->faker->firstName($gender),
            lastName: $lastName ?? $this->faker->lastName($gender),
            phoneNumber: $phoneNumber ?? $this->faker->regexify('/^\+380\d{9}$/'),
        );
    }

    /**
     * @return CustomerProfile[]
     */
    public function createMany(int $count): array
    {
        $attributes = [];
        for ($i = 0; $i < $count; ++$i) {
            $attributes[] = $this->create();
        }

        return $attributes;
    }
}
