<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\Entity;

use App\Catalog\Domain\Entity\AttributeOption;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\AttributeOption\ActiveFlag;
use App\Catalog\Domain\ValueObject\AttributeOption\Code;
use App\Catalog\Domain\ValueObject\AttributeOption\Metadata\DimensionMetadata;
use App\Catalog\Domain\ValueObject\AttributeOption\Translations;
use App\Catalog\Domain\ValueObject\AttributeOption\Ulid;
use App\Tests\Catalog\Support\AttributeOptionMother;
use PHPUnit\Framework\TestCase;

final class AttributeOptionTest extends TestCase
{
    public function testItCreatesValidAttributeOptionWithoutMetadata(): void
    {
        $ulid = Ulid::fromString(AttributeOptionMother::DEFAULT_ULID);
        $code = Code::fromString('white');
        $translations = Translations::fromArray(self::getValidTranslations());
        $activeFlag = ActiveFlag::fromBool(true);
        $adminUlid = AdminUlid::fromString(AttributeOptionMother::DEFAULT_ADMIN_ULID);

        $attributeOption = AttributeOption::create(
            ulid: $ulid,
            code: $code,
            translations: $translations,
            isActive: $activeFlag,
            createdBy: $adminUlid,
        );

        self::assertTrue($attributeOption->getUlid()->equals($ulid));
        self::assertTrue($attributeOption->getCode()->equals($code));
        self::assertCount($translations->count(), $attributeOption->getTranslations());
        foreach ($translations as $locale => $translation) {
            $actualTranslation = $attributeOption->getTranslations()->get($locale);
            self::assertNotNull($actualTranslation);
            self::assertSame($translation->value, $actualTranslation->value);
        }
        self::assertTrue($attributeOption->isActive()->equals($activeFlag));
        self::assertTrue($attributeOption->getCreatedBy()->equals($adminUlid));
        self::assertNull($attributeOption->getMetadata());
        self::assertNull($attributeOption->getUpdatedBy());
    }

    public function testItCreatesValidAttributeOptionWithMetadata(): void
    {
        $ulid = Ulid::fromString(AttributeOptionMother::DEFAULT_ULID);
        $code = Code::fromString('weight');
        $translations = Translations::fromArray(self::getValidTranslations());
        $activeFlag = ActiveFlag::fromBool(true);
        $adminUlid = AdminUlid::fromString(AttributeOptionMother::DEFAULT_ADMIN_ULID);
        $metadata = DimensionMetadata::fromFloat(DimensionMetadata::BASE_RATIO);

        $attributeOption = AttributeOption::create(
            ulid: $ulid,
            code: $code,
            translations: $translations,
            isActive: $activeFlag,
            createdBy: $adminUlid,
            metadata: $metadata,
        );

        self::assertTrue($attributeOption->getUlid()->equals($ulid));
        self::assertTrue($attributeOption->getCode()->equals($code));
        self::assertCount($translations->count(), $attributeOption->getTranslations());
        foreach ($translations as $locale => $translation) {
            $actualTranslation = $attributeOption->getTranslations()->get($locale);
            self::assertNotNull($actualTranslation);
            self::assertSame($translation->value, $actualTranslation->value);
        }
        self::assertTrue($attributeOption->isActive()->equals($activeFlag));
        self::assertTrue($attributeOption->getCreatedBy()->equals($adminUlid));
        self::assertTrue($attributeOption->getMetadata()->equals($metadata));
        self::assertNull($attributeOption->getUpdatedBy());
    }

    public function testItUpdateChangesStateWithoutMetadata(): void
    {
        $attributeOption = AttributeOptionMother::createWithData(code: 'old-code');

        $newCode = Code::fromString('new-code');
        $newTranslations = Translations::fromArray(self::getValidTranslations());
        $newActiveFlag = ActiveFlag::fromBool(false);
        $adminUlid = AdminUlid::fromString(AttributeOptionMother::DEFAULT_ADMIN_ULID);

        $attributeOption->update(
            code: $newCode,
            translations: $newTranslations,
            isActive: $newActiveFlag,
            updatedBy: $adminUlid,
        );

        self::assertTrue($attributeOption->getCode()->equals($newCode));
        self::assertCount($newTranslations->count(), $attributeOption->getTranslations());
        foreach ($newTranslations as $locale => $translation) {
            $actualTranslation = $attributeOption->getTranslations()->get($locale);
            self::assertNotNull($actualTranslation);
            self::assertSame($translation->value, $actualTranslation->value);
        }
        self::assertTrue($attributeOption->isActive()->equals($newActiveFlag));
        self::assertNull($attributeOption->getMetadata());
        self::assertTrue($attributeOption->getUpdatedBy()->equals($adminUlid));
    }

    public function testItUpdateChangesStateWithMetadata(): void
    {
        $attributeOption = AttributeOptionMother::createWithData(
            code: 'old-code',
            attributeType: TypeEnum::Dimension,
            metadata: 1.0
        );

        $newCode = Code::fromString('new-code');
        $newTranslations = Translations::fromArray(self::getValidTranslations());
        $newActiveFlag = ActiveFlag::fromBool(false);
        $adminUlid = AdminUlid::fromString(AttributeOptionMother::DEFAULT_ADMIN_ULID);
        $newMetadata = DimensionMetadata::fromFloat(1000.0);

        $attributeOption->update(
            code: $newCode,
            translations: $newTranslations,
            isActive: $newActiveFlag,
            updatedBy: $adminUlid,
            metadata: $newMetadata,
        );

        self::assertTrue($attributeOption->getCode()->equals($newCode));
        self::assertCount($newTranslations->count(), $attributeOption->getTranslations());
        foreach ($newTranslations as $locale => $translation) {
            $actualTranslation = $attributeOption->getTranslations()->get($locale);
            self::assertNotNull($actualTranslation);
            self::assertSame($translation->value, $actualTranslation->value);
        }
        self::assertTrue($attributeOption->isActive()->equals($newActiveFlag));
        self::assertTrue($attributeOption->getMetadata()->equals($newMetadata));
        self::assertTrue($attributeOption->getUpdatedBy()->equals($adminUlid));
    }

    private function getValidTranslations(): array
    {
        return [
            'en' => ['value' => 'Attribute option name'],
            'uk' => ['value' => 'Назва опції атрибуту'],
        ];
    }
}
