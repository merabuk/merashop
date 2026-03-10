<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use App\Shared\Domain\Enum\MimeTypeEnum;
use Imagick;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Throwable;

trait ApiFileUploadTrait
{
    public const string GD = 'gd';
    public const string IMAGICK = 'imagick';

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

    private function resolveMimeType(MimeTypeEnum|string $typeEnum): MimeTypeEnum
    {
        return $typeEnum instanceof MimeTypeEnum
            ? $typeEnum
            : MimeTypeEnum::tryFrom($typeEnum) ?? MimeTypeEnum::Unknown;
    }

    private function fillImageByLibrary(
        string $library,
        MimeTypeEnum $mimeType,
        string $tempPath,
        int $width,
        int $height,
    ): void {
        match ($library) {
            self::GD => $this->fillFakeGdImage($mimeType, $tempPath, $width, $height),
            self::IMAGICK => $this->getFakeImagickImage($mimeType, $tempPath, $width, $height),
            default => throw new RuntimeException('Unsupported image library'),
        };
    }

    private function createTempFilePath(string $prefix = 'test_', ?string $extension = null): string
    {
        $tempDir = sys_get_temp_dir();
        $fileName = $prefix.uniqid().($extension ? '.'.$extension : '');
        $fullPath = $tempDir.DIRECTORY_SEPARATOR.$fileName;

        touch($fullPath);

        return $fullPath;
    }

    private function fillFakeGdImage(
        MimeTypeEnum $mimeType,
        string $tempPath,
        int $width,
        int $height,
    ): void {
        if (!extension_loaded(self::GD)) {
            throw new RuntimeException('GD extension is not loaded');
        }
        try {
            $image = imagecreatetruecolor($width, $height);
            $background = imagecolorallocate($image, 255, 255, 255);
            imagefill($image, 0, 0, $background);

            match ($mimeType) {
                MimeTypeEnum::Jpg,
                MimeTypeEnum::Jpeg => imagejpeg($image, $tempPath),
                MimeTypeEnum::Png => imagepng($image, $tempPath),
                MimeTypeEnum::Webp => imagewebp($image, $tempPath),
                default => throw new RuntimeException(sprintf("Unsupported '%s' mimetype", $mimeType->value)),
            };
            imagedestroy($image);
        } catch (Throwable $e) {
            throw new RuntimeException($this->makeWriteErrorMessage(self::GD, $mimeType, $e));
        }
    }

    private function getFakeImagickImage(
        MimeTypeEnum $mimeType,
        string $tempPath,
        int $width,
        int $height,
    ): string {
        if (!extension_loaded(self::IMAGICK)) {
            throw new RuntimeException('Imagick extension is not loaded');
        }
        try {
            $image = new Imagick();
            $image->newImage($width, $height, 'white');
            $image->setImageFormat($mimeType->toExtension());

            $image->writeImage($tempPath);

            return $tempPath;
        } catch (Throwable $e) {
            throw new RuntimeException($this->makeWriteErrorMessage(self::IMAGICK, $mimeType, $e));
        }
    }

    private function makeWriteErrorMessage(string $library, MimeTypeEnum $mimeType, Throwable $e): string
    {
        return sprintf(
            "Failed to write fake '%s' image with '.%s' extension in temp path. Error: %s",
            $library,
            $mimeType->toExtension(),
            $e->getMessage()
        );
    }
}
