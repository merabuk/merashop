<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Id;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;

class Attribute
{
    /**
     * @param array<string, string> $translations [locale => name]
     */
    public function __construct(
        private readonly ?Id $id,
        private readonly Ulid $ulid,
        private Code $code,
        private TypeEnum $type,
        private array $translations = [],
    ) {
    }

    /**
     * @param array<string, string> $translations
     */
    public static function create(
        Ulid $ulid,
        Code $code,
        TypeEnum $type,
        array $translations = [],
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

    public function getType(): TypeEnum
    {
        return $this->type;
    }

    /**
     * @return array<string, string>
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    /**
     * @param array<string, string> $translations
     */
    public function update(Code $code, TypeEnum $type, array $translations): void
    {
        $this->code = $code;
        $this->type = $type;
        $this->translations = $translations;
    }
}
