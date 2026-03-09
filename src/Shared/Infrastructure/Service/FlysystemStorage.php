<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Service;

use App\Shared\Domain\Exception\Services\Storage\FileStorageException;
use App\Shared\Domain\Service\FileStorageInterface;
use App\Shared\Domain\ValueObject\RelativeFilePath;
use League\Flysystem\FilesystemOperator;
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
            $this->filesystem->writeStream($targetPath, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        } catch (Throwable $e) {
            throw $this->makeException('Fail writing stream', $e);
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
