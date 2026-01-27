<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Http\ApiVersion1\Resource\UserAccount;

final readonly class UserRegistrationResponse
{
    public function __construct(public string $message)
    {
    }
}
