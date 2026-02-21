<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\Entity;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Id;
use App\Catalog\Domain\ValueObject\Attribute\Translations;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;
use App\Catalog\Domain\ValueObject\Attribute\Version;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use PHPUnit\Framework\TestCase;

final class AttributeTest extends TestCase
{
    /**
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function testUpdateChangesState(): void
    {
        $attribute = $this->makeAttribute(code: 'old_code');
        $newCode = Code::fromString('new_code');
        $newType = Type::fromEnum(TypeEnum::Int);
        $translations = Translations::fromArray(['en' => ['name' => 'New name']]);
        $adminId = AdminUlid::fromString('01KHVRCC1Z9S7G603HEPK9MGEZ');

        $attribute->update(
            code: $newCode,
            type: $newType,
            translations: $attribute->getTranslations(),
            updatedBy: $adminId
        );

        self::assertSame($newCode->value(), $attribute->getCode()->value());
        self::assertSame($newType->value(), $attribute->getType()->value());
        foreach ($translations as $locale => $translation) {
            self::assertSame($translation->name, $attribute->getTranslations()->get($locale)->name);
        }
        self::assertSame($adminId->value(), $attribute->getUpdatedBy()?->value());
    }

    /**
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    private function makeAttribute(
        int $fakeId = 123,
        string $ulid = '01KHVRCA0FCCYAQT1P88R317DD',
        string $code = 'code',
        TypeEnum $type = TypeEnum::String,
        array $translations = ['en' => ['name' => 'Name']],
        int $version = 1,
        string $adminUlid = '01KHVRCA679BJ6PBXX5N3G6RR5',
    ): Attribute {
        return new Attribute(
            id: Id::fromInt($fakeId),
            ulid: Ulid::fromString($ulid),
            code: Code::fromString($code),
            type: Type::fromEnum($type),
            translations: Translations::fromArray($translations),
            version: Version::fromInt($version),
            createdBy: AdminUlid::fromString($adminUlid)
        );
    }
}
