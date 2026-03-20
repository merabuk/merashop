<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use App\Shared\Domain\Enum\MimeTypeEnum;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait ApiFileUploadTrait
{
    use ImageGeneratorTrait;
    use TempFileTrait;

    /**
     * @var string[]
     */
    private array $createdTempFiles = [];

    protected function createUploadedImage(
        MimeTypeEnum|string $mimeType = MimeTypeEnum::Jpeg,
        string $originalName = 'image.jpg',
        int $width = 100,
        int $height = 100,
        string $library = self::IMAGICK,
    ): UploadedFile {
        $mimeType = $this->resolveMimeType($mimeType);

        if (false === $mimeType->isImage()) {
            throw new RuntimeException('The provided MIME type is not for an image');
        }

        $tempPath = $this->createTempFilePath(prefix: 'test_img_', extension: $mimeType->toExtension());

        $this->fillImageByLibrary($library, $mimeType, $tempPath, $width, $height);

        $this->createdTempFiles[] = $tempPath;

        return new UploadedFile(
            path: $tempPath,
            originalName: $originalName,
            mimeType: $mimeType->value,
            error: null,
            test: true
        );
    }

    protected function createUploadedFile(
        MimeTypeEnum|string $mimeType,
        string $originalName,
        string $content,
    ): UploadedFile {
        $mimeType = $this->resolveMimeType($mimeType);

        if ($mimeType->isImage()) {
            throw new RuntimeException('For creating uploaded file use createUploadedImage method');
        }

        $tempPath = $this->createTempFilePath(prefix: 'test_file_', extension: $mimeType->toExtension());
        file_put_contents($tempPath, $content);
        $this->createdTempFiles[] = $tempPath;

        return new UploadedFile(
            path: $tempPath,
            originalName: $originalName,
            mimeType: $mimeType->value,
            error: null,
            test: true
        );
    }

    protected function cleanupUploadedFiles(): void
    {
        foreach ($this->createdTempFiles as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
        $this->createdTempFiles = [];
    }
}
