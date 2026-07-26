<?php

declare(strict_types=1);

namespace App\Customer\Presentation\Http\ApiVersion1\Request;

use App\Customer\Application\Command\UpdateCustomerProfile\UpdateCustomerProfileCommand;
use App\Customer\Domain\ValueObject\CustomerProfile\FirstName;
use App\Customer\Domain\ValueObject\CustomerProfile\LastName;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateCustomerProfileRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: FirstName::MAX_LENGTH)]
    public ?string $firstName = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: LastName::MAX_LENGTH)]
    public ?string $lastName = null;

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: "/^\+380\d{9}$/")]
    public ?string $phoneNumber = null;

    public function toCommand(string $userUlid): UpdateCustomerProfileCommand
    {
        return new UpdateCustomerProfileCommand(
            userUlid: $userUlid,
            firstName: $this->getFirstName(),
            lastName: $this->getLastName(),
            phoneNumber: $this->getPhoneNumber()
        );
    }

    public function getFirstName(): string
    {
        return (string) $this->firstName;
    }

    public function getLastName(): string
    {
        return (string) $this->lastName;
    }

    public function getPhoneNumber(): string
    {
        return (string) $this->phoneNumber;
    }
}
