<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use App\Shared\Domain\Enum\MimeTypeEnum;
use Imagick;
use RuntimeException;
use Throwable;

trait ImageGeneratorTrait
{
    public const string GD = 'gd';
    public const string IMAGICK = 'imagick';

    protected function resolveMimeType(MimeTypeEnum|string $typeEnum): MimeTypeEnum
    {
        return $typeEnum instanceof MimeTypeEnum
            ? $typeEnum
            : MimeTypeEnum::tryFrom($typeEnum) ?? MimeTypeEnum::Unknown;
    }

    protected function fillImageByLibrary(
        string $library,
        MimeTypeEnum $mimeType,
        string $path,
        int $width,
        int $height,
    ): void {
        match ($library) {
            self::GD => $this->fillFakeGdImage($mimeType, $path, $width, $height),
            self::IMAGICK => $this->getFakeImagickImage($mimeType, $path, $width, $height),
            default => throw new RuntimeException('Unsupported image library'),
        };
    }

    protected function generateFakeLargeJpeg(string $path, int $width, int $height): void
    {
        // Minimum valid JPEG header (1x1 pixel)
        $data = hex2bin('ffd8ffe000104a46494600010101004800480000ffdb004300080606070605080707070909080a0c140d0c0b0b0c1912130f141d1a1f1e1d1a1c1c20242e2720222c231c1c2837292c30313434341f27393d38323c2e333432ffc0001108');

        // Add the height and width (2 bytes per value)
        $data .= pack('n', $height);
        $data .= pack('n', $width);

        // Close the header structure
        $data .= hex2bin('03012200021101031101ffc4001f0000010501010101010100000000000000000102030405060708090a0bffc400b5100002010303020403050504040000017d010203000411051221314106135161072271810814238291a1091552b1c1d10a16243362728392a2b2c2d2e2f20b1718191a25262728292a3435363738393a434445464748494a535455565758595a636465666768696a737475767778797a8485868788898a939495969798999aa3a4a5a6a7a8a9aa');

        file_put_contents($path, $data);
    }

    private function fillFakeGdImage(
        MimeTypeEnum $mimeType,
        string $path,
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
                MimeTypeEnum::Jpeg => imagejpeg($image, $path),
                MimeTypeEnum::Png => imagepng($image, $path),
                MimeTypeEnum::Webp => imagewebp($image, $path),
                default => throw new RuntimeException(sprintf("Unsupported '%s' mimetype", $mimeType->value)),
            };
            imagedestroy($image);
        } catch (Throwable $e) {
            throw new RuntimeException($this->makeWriteErrorMessage(self::GD, $mimeType, $e));
        }
    }

    private function getFakeImagickImage(
        MimeTypeEnum $mimeType,
        string $path,
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

            $image->writeImage($path);

            return $path;
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
