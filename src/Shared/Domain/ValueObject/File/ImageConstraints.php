<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject\File;

class ImageConstraints
{
    /**
     * @param non-empty-string[] $allowedMimeTypes Allowed MIME types
     */
    public function __construct(
        public int $maxSize,
        public array $allowedMimeTypes,
        public ?int $minWidth = null,
        public ?int $minHeight = null,
        public ?int $maxWidth = null,
        public ?int $maxHeight = null,
        public bool $detectCorrupted = true,
    ) {
    }

    /**
     * @return int<1, max>
     */
    public function getMaxSize(): int
    {
        return max($this->maxSize, 1);
    }

    /**
     * @return int<1, max>|null
     */
    public function getMaxWidth(): ?int
    {
        return is_null($this->maxWidth) ? null : max($this->maxWidth, 1);
    }

    /**
     * @return int<1, max>|null
     */
    public function getMaxHeight(): ?int
    {
        return is_null($this->maxHeight) ? null : max($this->maxHeight, 1);
    }
}
