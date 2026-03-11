<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Service;

use App\Catalog\Domain\Service\CatalogStorageInterface;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid;
use App\Shared\Domain\Exception\Services\Storage\FileStorageException;
use App\Shared\Domain\Exception\ValueObject\InvalidRelativePathException;
use App\Shared\Domain\ValueObject\File\RawFile;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;
use App\Shared\Infrastructure\Service\Storage\FlysystemStorage;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Clock\ClockInterface;

final readonly class CatalogFlysystemStorage extends FlysystemStorage implements CatalogStorageInterface
{
    public function __construct(
        private ClockInterface $clock,
        FilesystemOperator $filesystem,
    ) {
        parent::__construct($filesystem);
    }

    public function getPublicUrl(RelativeFilePath $path): string
    {
        // TODO: Implement getPublicUrl() method.
        return $path->value();
    }

    /**
     * @throws FileStorageException
     */
    public function generateTemporaryImageStoragePath(
        Ulid $ulid,
        RawFile $file,
    ): RelativeFilePath {
        try {
            $fileName = sprintf('%s.%s', $ulid->value(), $file->getExtension());

            $path = implode(RelativeFilePath::SEPARATOR, [
                'temp',
                $this->clock->now()->format('Y'),
                $this->clock->now()->format('m'),
                $this->clock->now()->format('d'),
                $ulid->value(),
                $fileName,
            ]);

            return RelativeFilePath::fromString($path);
        } catch (InvalidRelativePathException $e) {
            throw new FileStorageException(message: 'Failed to generate temporary image storage path', previous: $e);
        }
    }
}
