<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service;

use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid;
use App\Shared\Domain\Exception\Services\Storage\FileStorageException;
use App\Shared\Domain\Service\Storage\FileStorageInterface;
use App\Shared\Domain\ValueObject\File\RawFile;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;

interface CatalogStorageInterface extends FileStorageInterface
{
    /**
     * @throws FileStorageException
     */
    public function generateTemporaryImageStoragePath(Ulid $ulid, RawFile $file): RelativeFilePath;

    public function generateProductImageStoragePath(RelativeFilePath $temporaryImageStoragePath): RelativeFilePath;
}
