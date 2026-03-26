<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Entity;

use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionVersionException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\AttributeOption\ActiveFlag;
use App\Catalog\Domain\ValueObject\AttributeOption\Code;
use App\Catalog\Domain\ValueObject\AttributeOption\Id;
use App\Catalog\Domain\ValueObject\AttributeOption\Metadata\AttributeOptionMetadataInterface;
use App\Catalog\Domain\ValueObject\AttributeOption\Translations;
use App\Catalog\Domain\ValueObject\AttributeOption\Ulid;
use App\Catalog\Domain\ValueObject\AttributeOption\Version;

class AttributeOption
{
    public function __construct(
        private readonly Ulid $ulid,
        private Code $code,
        private Translations $translations,
        private ActiveFlag $isActive,
        private readonly Version $version,
        private readonly AdminUlid $createdBy,
        private ?AttributeOptionMetadataInterface $metadata = null,
        private ?AdminUlid $updatedBy = null,
        private readonly ?Id $id = null,
    ) {
    }

    /**
     * @throws InvalidAttributeOptionVersionException
     */
    public static function create(
        Ulid $ulid,
        Code $code,
        Translations $translations,
        ActiveFlag $isActive,
        AdminUlid $createdBy,
        ?AttributeOptionMetadataInterface $metadata = null,
    ): self {
        return new self(
            ulid: $ulid,
            code: $code,
            translations: $translations,
            isActive: $isActive,
            version: Version::initial(),
            createdBy: $createdBy,
            metadata: $metadata,
        );
    }

    public function update(
        Code $code,
        Translations $translations,
        ActiveFlag $isActive,
        AdminUlid $updatedBy,
        ?AttributeOptionMetadataInterface $metadata = null,
    ): void {
        $this->code = $code;
        $this->translations = $translations;
        $this->isActive = $isActive;
        $this->metadata = $metadata;
        $this->updatedBy = $updatedBy;
    }

    public function deactivate(AdminUlid $adminUlid): void
    {
        $this->isActive = ActiveFlag::inactive();
        $this->updatedBy = $adminUlid;
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

    public function getTranslations(): Translations
    {
        return $this->translations;
    }

    public function isActive(): ActiveFlag
    {
        return $this->isActive;
    }

    public function getVersion(): Version
    {
        return $this->version;
    }

    public function getCreatedBy(): AdminUlid
    {
        return $this->createdBy;
    }

    public function getMetadata(): ?AttributeOptionMetadataInterface
    {
        return $this->metadata;
    }

    public function getUpdatedBy(): ?AdminUlid
    {
        return $this->updatedBy;
    }
}
