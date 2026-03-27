<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\Exception\Attribute\AttributeStateException;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeVersionException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Id;
use App\Catalog\Domain\ValueObject\Attribute\OptionCollection;
use App\Catalog\Domain\ValueObject\Attribute\Translations;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;
use App\Catalog\Domain\ValueObject\Attribute\Version;
use App\Shared\Domain\Entity\HasIdInterface;

class Attribute implements HasIdInterface
{
    /**
     * @throws AttributeStateException
     */
    public function __construct(
        private readonly Ulid $ulid,
        private Code $code,
        private Type $type,
        private Translations $translations,
        private readonly Version $version,
        private readonly AdminUlid $createdBy,
        private OptionCollection $options,
        private ?AdminUlid $updatedBy = null,
        private readonly ?Id $id = null,
    ) {
        $this->ensureTypeAndOptionsConsistency();
    }

    /**
     * @throws AttributeStateException
     * @throws InvalidAttributeVersionException
     */
    public static function create(
        Ulid $ulid,
        Code $code,
        Type $type,
        Translations $translations,
        AdminUlid $createdBy,
        OptionCollection $options,
    ): self {
        return new self(
            ulid: $ulid,
            code: $code,
            type: $type,
            translations: $translations,
            version: Version::initial(),
            createdBy: $createdBy,
            options: $options,
        );
    }

    /**
     * @throws AttributeStateException
     */
    public function update(
        Code $code,
        Type $type,
        Translations $translations,
        AdminUlid $updatedBy,
        OptionCollection $options,
    ): void {
        if (false === $this->type->allowChange($type)) {
            throw AttributeStateException::becauseTypeCanNotBeChanged((string) $this->type, (string) $type);
        }

        $this->code = $code;
        $this->type = $type;
        $this->translations = $translations;
        $this->updatedBy = $updatedBy;
        $this->options = $options;

        $this->ensureTypeAndOptionsConsistency();
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

    public function getVersion(): Version
    {
        return $this->version;
    }

    public function getCreatedBy(): AdminUlid
    {
        return $this->createdBy;
    }

    public function getOptions(): OptionCollection
    {
        return $this->options;
    }

    public function getUpdatedBy(): ?AdminUlid
    {
        return $this->updatedBy;
    }

    /**
     * @throws AttributeStateException
     */
    private function ensureTypeAndOptionsConsistency(): void
    {
        if (false === $this->options->isInitialized()) {
            return;
        }

        if ($this->type->hasOptions() && $this->options->isEmpty()) {
            throw AttributeStateException::becauseOptionsRequiredForType((string) $this->type);
        }

        if (!$this->type->hasOptions() && !$this->options->isEmpty()) {
            throw AttributeStateException::becauseOptionsNotAllowedForType((string) $this->type);
        }
    }
}
