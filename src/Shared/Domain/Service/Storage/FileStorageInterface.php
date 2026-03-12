<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service\Storage;

use App\Shared\Domain\Exception\Services\Storage\FileStorageException;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;

interface FileStorageInterface
{
    /**
     * @throws FileStorageException
     */
    public function uploadFromLocalPath(string $localPath, string $targetPath): void;

    /**
     * @throws FileStorageException
     */
    public function move(string $sourcePath, string $targetPath): void;

    /**
     * @param resource|string $content
     *
     * @throws FileStorageException
     */
    public function upload(string $path, mixed $content): void;

    /**
     * @throws FileStorageException
     */
    public function delete(RelativeFilePath $path): void;

    /**
     * @throws FileStorageException
     */
    public function exists(RelativeFilePath $path): bool;

    public function getPublicUrl(RelativeFilePath $path): string;
}
