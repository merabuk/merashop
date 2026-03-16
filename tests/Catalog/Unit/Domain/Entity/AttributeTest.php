<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\Entity;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Translations;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;
use App\Tests\Catalog\Support\AttributeMother;
use PHPUnit\Framework\TestCase;

final class AttributeTest extends TestCase
{
    public function testItCreatesValidAttribute(): void
    {
        $ulid = Ulid::fromString(AttributeMother::DEFAULT_ULID);
        $code = Code::fromString('color');
        $type = Type::string();
        $translations = Translations::fromArray(self::getValidTranslations());
        $adminUlid = AdminUlid::fromString(AttributeMother::DEFAULT_ADMIN_ULID);

        $attribute = Attribute::create(
            ulid: $ulid,
            code: $code,
            type: $type,
            translations: $translations,
            createdBy: $adminUlid
        );

        self::assertNull($attribute->getId());
        self::assertTrue($attribute->getUlid()->equals($ulid));
        self::assertTrue($attribute->getCode()->equals($code));
        self::assertTrue($attribute->getType()->equals($type));
        self::assertCount($translations->count(), $attribute->getTranslations());
        foreach ($translations as $locale => $translation) {
            $actualTranslation = $attribute->getTranslations()->get($locale);
            self::assertNotNull($actualTranslation);
            self::assertSame($translation->name, $actualTranslation->name);
        }
        self::assertSame(1, $attribute->getVersion()->value());
        self::assertTrue($attribute->getCreatedBy()->equals($adminUlid));
        self::assertNull($attribute->getUpdatedBy());
    }

    public function testUpdateChangesState(): void
    {
        $attribute = AttributeMother::createWithData(code: 'old_code');

        $newCode = Code::fromString('new_code');
        $newType = Type::fromEnum(TypeEnum::Int);
        $newTranslations = Translations::fromArray(self::getValidTranslations());
        $adminUlid = AdminUlid::fromString('01KHVRCC1Z9S7G603HEPK9MGEZ');

        $attribute->update(
            code: $newCode,
            type: $newType,
            translations: $newTranslations,
            updatedBy: $adminUlid
        );

        self::assertTrue($attribute->getCode()->equals($newCode));
        self::assertTrue($attribute->getType()->equals($newType));
        self::assertCount($newTranslations->count(), $attribute->getTranslations());
        foreach ($newTranslations as $locale => $translation) {
            $actualTranslation = $attribute->getTranslations()->get($locale);
            self::assertNotNull($actualTranslation);
            self::assertSame($translation->name, $actualTranslation->name);
        }
        self::assertTrue($attribute->getUpdatedBy()?->equals($adminUlid));
    }

    private function getValidTranslations(): array
    {
        return [
            'en' => ['name' => 'Color'],
            'uk' => ['name' => 'Колір'],
        ];
    }
}
