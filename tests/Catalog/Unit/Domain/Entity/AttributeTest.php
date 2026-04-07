<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\Entity;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\Attribute\AttributeStateException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\OptionCollection;
use App\Catalog\Domain\ValueObject\Attribute\Translations;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;
use App\Tests\Catalog\Support\AttributeMother;
use App\Tests\Catalog\Support\AttributeOptionMother;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AttributeTest extends TestCase
{
    public function testItCreatesValidAttributeWithoutOptions(): void
    {
        $ulid = Ulid::fromString(AttributeMother::DEFAULT_ULID);
        $code = Code::fromString('color');
        $type = Type::fromEnum(TypeEnum::Color);
        $translations = Translations::fromArray(self::getValidTranslations());
        $adminUlid = AdminUlid::fromString(AttributeMother::DEFAULT_ADMIN_ULID);
        $options = OptionCollection::empty();

        $attribute = Attribute::create(
            ulid: $ulid,
            code: $code,
            type: $type,
            translations: $translations,
            createdBy: $adminUlid,
            options: $options
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
        self::assertCount($options->count(), $attribute->getOptions());
        self::assertTrue($attribute->getOptions()->equals($options));
        self::assertNull($attribute->getUpdatedBy());
    }

    public function testItCreatesValidAttributeWithOptions(): void
    {
        $ulid = Ulid::fromString(AttributeMother::DEFAULT_ULID);
        $code = Code::fromString('wireless-technology');
        $type = Type::fromEnum(TypeEnum::MultiSelect);
        $translations = Translations::fromArray(self::getValidTranslations());
        $adminUlid = AdminUlid::fromString(AttributeMother::DEFAULT_ADMIN_ULID);
        $options = OptionCollection::fromArray([
            AttributeOptionMother::createWithData(
                ulid: '01KMDEC4Z9NSK4YPEW8NG5068T',
                code: 'bluetooth',
            ),
            AttributeOptionMother::createWithData(
                ulid: '01KMGY62KTY8BJ9J8NHMXHKF4P',
                code: 'wifi',
            ),
            AttributeOptionMother::createWithData(
                ulid: '01KMJ1ANFES9VS1HYEYBDCNCFW',
                code: 'nfc',
            ),
        ]);

        $attribute = Attribute::create(
            ulid: $ulid,
            code: $code,
            type: $type,
            translations: $translations,
            createdBy: $adminUlid,
            options: $options
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
        self::assertCount($options->count(), $attribute->getOptions());
        self::assertTrue($attribute->getOptions()->equals($options));
        self::assertNull($attribute->getUpdatedBy());
    }

    #[DataProvider('invalidCreateStateProvider')]
    public function testThrowsExceptionWhenAttributeHasInvalidStateWhileCreate(
        TypeEnum $type,
        OptionCollection $options,
    ): void {
        $this->expectException(AttributeStateException::class);

        Attribute::create(
            ulid: Ulid::fromString(AttributeMother::DEFAULT_ULID),
            code: Code::fromString('color'),
            type: Type::fromEnum($type),
            translations: Translations::fromArray(self::getValidTranslations()),
            createdBy: AdminUlid::fromString(AttributeMother::DEFAULT_ADMIN_ULID),
            options: $options
        );
    }

    public static function invalidCreateStateProvider(): iterable
    {
        yield 'type which cannot have options' => [
            'type' => TypeEnum::Color,
            'options' => OptionCollection::fromArray([
                AttributeOptionMother::createWithData(),
            ]),
        ];
        yield 'type which can have options' => [
            'type' => TypeEnum::MultiSelect,
            'options' => OptionCollection::empty(),
        ];
    }

    #[DataProvider('updateStateProvider')]
    public function testItUpdateChangesState(
        TypeEnum $fromType,
        TypeEnum $toType,
        OptionCollection $options,
    ): void {
        $attribute = AttributeMother::createWithData(code: 'old-code', type: $fromType);

        $newCode = Code::fromString('new-code');
        $newType = Type::fromEnum($toType);
        $newTranslations = Translations::fromArray(self::getValidTranslations());
        $adminUlid = AdminUlid::fromString(AttributeMother::DEFAULT_ADMIN_ULID);

        $attribute->update(
            code: $newCode,
            type: $newType,
            translations: $newTranslations,
            updatedBy: $adminUlid,
            options: $options,
        );

        self::assertTrue($attribute->getCode()->equals($newCode));
        self::assertTrue($attribute->getType()->equals($newType));
        self::assertTrue($attribute->getTranslations()->equals($newTranslations));
        self::assertTrue($attribute->getUpdatedBy()->equals($adminUlid));
        self::assertTrue($attribute->getOptions()->equals($options));
    }

    public static function updateStateProvider(): iterable
    {
        yield 'string to text' => [
            'fromType' => TypeEnum::String,
            'toType' => TypeEnum::Text,
            'options' => OptionCollection::empty(),
        ];
        yield 'text to string' => [
            'fromType' => TypeEnum::Text,
            'toType' => TypeEnum::String,
            'options' => OptionCollection::empty(),
        ];
        yield 'change options' => [
            'fromType' => TypeEnum::Dimension,
            'toType' => TypeEnum::Dimension,
            'options' => OptionCollection::fromArray([
                AttributeOptionMother::createWithData(attributeType: TypeEnum::Dimension, metadata: 1000.0),
            ]),
        ];
    }

    #[DataProvider('invalidUpdateStateProvider')]
    public function testThrowsExceptionWhenAttributeHasInvalidStateWhileUpdate(
        Attribute $attribute,
        TypeEnum $toType,
        OptionCollection $options,
    ): void {
        $this->expectException(AttributeStateException::class);

        $attribute->update(
            code: Code::fromString('new-code'),
            type: Type::fromEnum($toType),
            translations: Translations::fromArray(self::getValidTranslations()),
            updatedBy: AdminUlid::fromString(AttributeMother::DEFAULT_ADMIN_ULID),
            options: $options,
        );
    }

    public static function invalidUpdateStateProvider(): iterable
    {
        yield 'cannot change type' => [
            'attribute' => AttributeMother::createWithData(type: TypeEnum::Color),
            'toType' => TypeEnum::String,
            'options' => OptionCollection::empty(),
        ];
        yield 'type which cannot have options' => [
            'attribute' => AttributeMother::createWithData(type: TypeEnum::Color),
            'toType' => TypeEnum::Color,
            'options' => OptionCollection::fromArray([
                AttributeOptionMother::createWithData(),
            ]),
        ];
        yield 'type which can have options' => [
            'attribute' => AttributeMother::createWithData(type: TypeEnum::MultiSelect),
            'toType' => TypeEnum::MultiSelect,
            'options' => OptionCollection::empty(),
        ];
    }

    private function getValidTranslations(): array
    {
        return [
            'en' => ['name' => 'Attribute name'],
            'uk' => ['name' => 'Назва атрибуту'],
        ];
    }
}
