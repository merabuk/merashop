<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Service\Storage;

use App\Shared\Domain\Exception\Services\Storage\FileStorageException;
use App\Shared\Domain\Service\Storage\FileStorageInterface;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;
use League\Flysystem\FilesystemOperator;
use RuntimeException;
use Throwable;

abstract readonly class FlysystemStorage implements FileStorageInterface
{
    public function __construct(
        protected FilesystemOperator $filesystem,
    ) {
    }

    public function uploadFromLocalPath(string $localPath, string $targetPath): void
    {
        try {
            $stream = fopen($localPath, 'rb');

            if (false === $stream) {
                throw new RuntimeException('Fail opening file: '.$localPath);
            }

            $this->filesystem->writeStream($targetPath, $stream);
        } catch (Throwable $e) {
            throw $this->makeException('Fail writing stream', $e);
        } finally {
            if (isset($stream) && is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    public function move(string $sourcePath, string $targetPath): void
    {
        try {
            $this->filesystem->move($sourcePath, $targetPath);
        } catch (Throwable $e) {
            throw $this->makeException(sprintf('Fail moving file from %s to %s', $sourcePath, $targetPath), $e);
        }
    }

    /**
     * @param resource|string $content
     *
     * @throws FileStorageException
     */
    public function upload(string $path, mixed $content): void
    {
        try {
            $this->filesystem->write($path, $content);
        } catch (Throwable $e) {
            throw $this->makeException('Fail writing file', $e);
        }
    }

    /**
     * @throws FileStorageException
     */
    public function delete(RelativeFilePath $path): void
    {
        try {
            $this->filesystem->delete($path->value());
        } catch (Throwable $e) {
            throw $this->makeException('Fail deleting file', $e);
        }
    }

    /**
     * @throws FileStorageException
     */
    public function exists(RelativeFilePath $path): bool
    {
        try {
            return $this->filesystem->has($path->value());
        } catch (Throwable $e) {
            throw $this->makeException('Fail checking file existence', $e);
        }
    }

    protected function makeException(string $message, Throwable $e): FileStorageException
    {
        return new FileStorageException(sprintf('%s. Previous: %s', $message, $e->getMessage()), previous: $e);
    }
}
