<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Resource\Product;

final readonly class UpdateProductResponse
{
    public function __construct(public string $message)
    {
    }
}
