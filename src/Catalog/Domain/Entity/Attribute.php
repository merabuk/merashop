<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Id;
use App\Catalog\Domain\ValueObject\Attribute\Translations;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;

class Attribute
{
    public function __construct(
        private readonly ?Id $id,
        private readonly Ulid $ulid,
        private Code $code,
        private Type $type,
        private Translations $translations,
    ) {
    }

    public static function create(
        Ulid $ulid,
        Code $code,
        Type $type,
        Translations $translations,
    ): self {
        return new self(
            id: null,
            ulid: $ulid,
            code: $code,
            type: $type,
            translations: $translations,
        );
    }

    public function getId(): ?Id
    {
        return $this->id;
    }

    public function getUlid(): Ulid
    {
        return $this->ulid;
    }

    public function getCode(): Code
    {
        return $this->code;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getTranslations(): Translations
    {
        return $this->translations;
    }

    public function update(Code $code, Type $type, Translations $translations): void
    {
        $this->code = $code;
        $this->type = $type;
        $this->translations = $translations;
    }
}
