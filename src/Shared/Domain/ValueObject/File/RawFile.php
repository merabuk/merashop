<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject\File;

use App\Shared\Domain\Exception\ValueObject\InvalidRawFileException;

final readonly class RawFile
{
    public const string DEFAULT_EXTENSION = 'bin';
    public const string DEFAULT_MIME_TYPE = 'application/octet-stream';

    /**
     * @throws InvalidRawFileException
     */
    private function __construct(
        private string $localPath,
        private string $originalName,
        private string $extension,
        private string $mimeType,
    ) {
        if (!is_file($this->localPath)) {
            throw InvalidRawFileException::fileNotFound($this->localPath);
        }

        if (!is_readable($this->localPath)) {
            throw InvalidRawFileException::fileNotReadable($this->localPath);
        }
    }

    /**
     * @throws InvalidRawFileException
     */
    public static function fromPath(
        string $localPath,
        string $originalName,
        ?string $extension = null,
        ?string $mimeType = null,
    ): self {
        return new self(
            localPath: $localPath,
            originalName: $originalName,
            extension: $extension ?? self::DEFAULT_EXTENSION,
            mimeType: $mimeType ?? self::DEFAULT_MIME_TYPE
        );
    }

    public function getLocalPath(): string
    {
        return $this->localPath;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }
}
