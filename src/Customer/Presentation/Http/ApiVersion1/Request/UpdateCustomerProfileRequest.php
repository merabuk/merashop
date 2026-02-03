<?php

declare(strict_types=1);

namespace App\Customer\Presentation\Http\ApiVersion1\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateCustomerProfileRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 60)]
    public ?string $firstName;

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 60)]
    public ?string $lastName;

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: "/^\+380\d{9}$/")]
    public ?string $phoneNumber;
}
