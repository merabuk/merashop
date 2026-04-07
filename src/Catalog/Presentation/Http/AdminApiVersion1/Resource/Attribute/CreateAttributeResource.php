<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Resource\Attribute;

final readonly class CreateAttributeResource
{
    public function __construct(public string $message)
    {
    }
}
