<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Http\ApiVersion1\Resource;

final readonly class RevokeTokenResponse
{
    public function __construct(public string $message)
    {
    }
}
