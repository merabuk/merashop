<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

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
        ?string $extension = self::DEFAULT_EXTENSION,
        ?string $mimeType = self::DEFAULT_MIME_TYPE,
    ): self {
        return new self($localPath, $originalName, $extension, $mimeType);
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
