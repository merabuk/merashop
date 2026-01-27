<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Http\ApiVersion1\Request\UserAccount;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class RegisterUserAccountRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public ?string $email,
        #[Assert\NotBlank]
        #[Assert\Length(min: 8)]
        #[Assert\PasswordStrength]
        public ?string $password,
        #[Assert\NotBlank]
        #[Assert\EqualTo(propertyPath: 'password')]
        public ?string $passwordConfirmation,
        #[Assert\NotBlank]
        #[Assert\IsTrue]
        public ?bool $termsAccepted = false,
    ) {
    }
}
