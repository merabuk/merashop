<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Resource\TemporaryImage;

final readonly class UploadedTemporaryImageResponse
{
    public function __construct(
        public string $message,
        public string $imageId,
    ) {
    }
}
