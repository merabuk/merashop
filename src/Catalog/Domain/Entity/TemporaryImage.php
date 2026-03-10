<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\ValueObject\TemporaryImage\Context;
use App\Catalog\Domain\ValueObject\TemporaryImage\Id;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;

readonly class TemporaryImage
{
    public function __construct(
        private Ulid $ulid,
        private RelativeFilePath $path,
        private Context $context,
        private ?Id $id = null,
    ) {
    }

    public static function create(
        Ulid $ulid,
        RelativeFilePath $path,
        Context $context,
    ): self {
        return new self(ulid: $ulid, path: $path, context: $context);
    }

    public function getUlid(): Ulid
    {
        return $this->ulid;
    }

    public function getPath(): RelativeFilePath
    {
        return $this->path;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getId(): ?Id
    {
        return $this->id;
    }
}
