<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Resource\Category;

final readonly class CreateCategoryResponse
{
    public function __construct(public string $message)
    {
    }
}
