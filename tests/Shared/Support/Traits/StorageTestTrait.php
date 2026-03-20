<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use App\Shared\Domain\Enum\MimeTypeEnum;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\Assert;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @mixin WebTestCase
 */
trait StorageTestTrait
{
    use ImageGeneratorTrait;
    use TempFileTrait;

    public const int MAX_IMAGE_WIDTH = 5000;
    public const int MAX_IMAGE_HEIGHT = 5000;

    protected function putImageToStorage(
        string $path,
        int $width = 100,
        int $height = 100,
        MimeTypeEnum $mimeType = MimeTypeEnum::Jpeg,
        string $library = self::IMAGICK,
        ?string $storageServiceId = null,
    ): void {
        if (false === $mimeType->isImage()) {
            throw new RuntimeException('The provided MIME type is not for an image');
        }

        $tempPath = $this->createTempFilePath(prefix: 'test_storage_gen_');

        if ($width > self::MAX_IMAGE_WIDTH || $height > self::MAX_IMAGE_HEIGHT) {
            $this->generateFakeLargeJpeg($tempPath, $width, $height);
        } else {
            $this->fillImageByLibrary($library, $mimeType, $tempPath, $width, $height);
        }

        $content = file_get_contents($tempPath);
        unlink($tempPath);

        $this->getStorage($storageServiceId)->write($path, $content);
    }

    protected function putToStorage(
        string $path,
        string $content = 'fake_binary_data',
        ?string $storageServiceId = null,
    ): void {
        $storage = $this->getStorage($storageServiceId);
        $storage->write($path, $content);
    }

    protected function assertStorageHas(string $path, ?string $storageServiceId = null): void
    {
        $storage = $this->getStorage($storageServiceId);
        Assert::assertTrue($storage->has($path), "File not found in storage: {$path}");
    }

    protected function getStorage(?string $storageServiceId = null): FilesystemOperator
    {
        return self::getContainer()->get($storageServiceId ?? $this->getStorageServiceId());
    }

    abstract protected function getStorageServiceId(): string;
}
