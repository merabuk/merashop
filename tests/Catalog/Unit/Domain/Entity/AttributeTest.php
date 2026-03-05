<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\Entity;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Translations;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Tests\Catalog\Support\AttributeMother;
use PHPUnit\Framework\TestCase;

final class AttributeTest extends TestCase
{
    public function testUpdateChangesState(): void
    {
        $attribute = $this->makeAttribute(code: 'old_code');

        $newCode = Code::fromString('new_code');
        $newType = Type::fromEnum(TypeEnum::Int);
        $newTranslations = Translations::fromArray(['en' => ['name' => 'New name']]);
        $adminId = AdminUlid::fromString('01KHVRCC1Z9S7G603HEPK9MGEZ');

        $attribute->update(
            code: $newCode,
            type: $newType,
            translations: $newTranslations,
            updatedBy: $adminId
        );

        self::assertTrue($attribute->getCode()->equals($newCode));
        self::assertTrue($attribute->getType()->equals($newType));
        self::assertCount($newTranslations->count(), $attribute->getTranslations());
        foreach ($newTranslations as $locale => $translation) {
            self::assertSame($translation->name, $attribute->getTranslations()->get($locale)->name);
        }
        self::assertTrue($attribute->getUpdatedBy()?->equals($adminId));
    }

    private function makeAttribute(string $code = 'code'): Attribute
    {
        return AttributeMother::createWithData(code: $code);
    }
}
