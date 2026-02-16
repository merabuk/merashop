<?php

declare(strict_types=1);

namespace App\Customer\Presentation\Http\ApiVersion1\Request;

use App\Customer\Domain\ValueObject\CustomerProfile\FirstName;
use App\Customer\Domain\ValueObject\CustomerProfile\LastName;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateCustomerProfileRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: FirstName::MAX_LENGTH)]
    public ?string $firstName;

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: LastName::MAX_LENGTH)]
    public ?string $lastName;

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: "/^\+380\d{9}$/")]
    public ?string $phoneNumber;
}
