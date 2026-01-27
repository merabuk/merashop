<?php

namespace App\Users\Presentation\Http\ApiVersion1\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UserRegisterRequest
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 8, max: 64)]
    #[Assert\PasswordStrength]
    public string $password;

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 60)]
    public string $firstName;

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 60)]
    public string $lastName;

    #[Assert\Regex(pattern: "/^\+380\d{9}$/")]
    public ?string $phoneNumber;
}
