<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enum;

enum MimeTypeEnum: string
{
    use StringEnumTrait;

    case Jpg = 'image/jpg';
    case Jpeg = 'image/jpeg';
    case Png = 'image/png';
    case Webp = 'image/webp';
    case Gif = 'image/gif';
    case Pdf = 'application/pdf';
    case Unknown = 'application/octet-stream';

    public function toExtension(): string
    {
        return match ($this) {
            self::Jpg, self::Jpeg => 'jpg',
            self::Png => 'png',
            self::Webp => 'webp',
            self::Gif => 'gif',
            self::Pdf => 'pdf',
            default => 'bin',
        };
    }

    public function isImage(): bool
    {
        return in_array($this, self::getImageCases(), true);
    }

    /**
     * @return self[]
     */
    public static function getImageCases(): array
    {
        return [
            self::Jpg,
            self::Jpeg,
            self::Png,
            self::Webp,
            self::Gif,
        ];
    }

    /**
     * @return string[]
     */
    public static function getImageValues(): array
    {
        return self::getValues(self::getImageCases());
    }
}
