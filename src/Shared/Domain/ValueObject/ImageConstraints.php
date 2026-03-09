<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

class ImageConstraints
{
    /**
     * @param string[] $allowedMimeTypes Allowed MIME types
     */
    public function __construct(
        public int $maxSize,
        public array $allowedMimeTypes,
        public ?int $maxWidth = null,
        public ?int $maxHeight = null,
        public bool $detectCorrupted = true,
    ) {
    }
}
