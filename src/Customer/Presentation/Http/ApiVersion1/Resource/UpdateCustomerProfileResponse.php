<?php

declare(strict_types=1);

namespace App\Customer\Presentation\Http\ApiVersion1\Resource;

final readonly class UpdateCustomerProfileResponse
{
    public function __construct(public string $message)
    {
    }
}
