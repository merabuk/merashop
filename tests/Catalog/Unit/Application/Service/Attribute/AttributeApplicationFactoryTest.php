<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Service\Attribute;

use App\Catalog\Application\Service\Attribute\AttributeApplicationFactory;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Tests\Catalog\Support\AttributeMother;
use App\Tests\Catalog\Support\Traits\AttributeHelperTrait;
use App\Tests\Shared\Support\Traits\UlidGenerationTrait;
use PHPUnit\Framework\TestCase;

final class AttributeApplicationFactoryTest extends TestCase
{
    use AttributeHelperTrait;
    use UlidGenerationTrait;

    protected function setUp(): void
    {
        $this->setUlidGenerator();
    }

    public function testItCreatesFromCommandWithoutOptions(): void
    {
        $attribute = AttributeMother::createWithData(type: TypeEnum::String);
        $command = $this->fillAndGetCreateCommand($attribute);

        $this->expectGenerateUlid($attribute->getUlid()->value());

        $result = $this->createFactory()->createFromCommand($command);

        self::assertTrue($attribute->getUlid()->equals($result->getUlid()));
        self::assertTrue($attribute->getCode()->equals($result->getCode()));
        self::assertTrue($attribute->getType()->equals($result->getType()));
        self::assertTrue($attribute->getTranslations()->equals($result->getTranslations()));
        self::assertSame(1, $result->getVersion());
        self::assertTrue($attribute->getCreatedBy()->equals($result->getCreatedBy()));
        self::assertCount(0, $result->getOptions());
        self::assertTrue($attribute->getOptions()->equals($result->getOptions()));
        self::assertNull($result->getUpdatedBy());
    }

    public function testItCreatesFromCommandWithOptions(): void
    {
        $attribute = AttributeMother::createWithData(type: TypeEnum::MultiSelect);
        $command = $this->fillAndGetCreateCommand($attribute);

        $this->expectGenerateUlid($attribute->getUlid()->value());

        $result = $this->createFactory()->createFromCommand($command);

        self::assertTrue($attribute->getUlid()->equals($result->getUlid()));
        self::assertTrue($attribute->getCode()->equals($result->getCode()));
        self::assertTrue($attribute->getType()->equals($result->getType()));
        self::assertTrue($attribute->getTranslations()->equals($result->getTranslations()));
        self::assertSame(1, $result->getVersion());
        self::assertTrue($attribute->getCreatedBy()->equals($result->getCreatedBy()));
        self::assertTrue($attribute->getOptions()->count() > 0);
        self::assertTrue($attribute->getOptions()->equals($result->getOptions()));
        self::assertNull($result->getUpdatedBy());
    }

    public function testItUpdatesFromCommandWithoutOptions(): void
    {
        $type = TypeEnum::Integer;
        $attribute = AttributeMother::createWithData(
            ulid: AttributeMother::DEFAULT_ULID,
            code: 'old-code',
            type: $type,
            translations: [
                'en' => ['name' => 'Old Attribute'],
            ],
            version: 2,
            createdByUlid: AttributeMother::DEFAULT_ADMIN_ULID,
            options: [],
            id: 123
        );
        $attributeForUpdate = AttributeMother::createWithData(
            ulid: '01KME5MMDTM1607C5T3X8YHDB0',
            type: $type,
            version: 1,
            createdByUlid: '01KM2VXDT53FFTM8A520WFDK9T',
            options: [],
            updatedByUlid: AttributeMother::DEFAULT_ADMIN_ULID,
            id: 456
        );
        $command = $this->fillAndGetUpdateCommand($attributeForUpdate);

        $this->createFactory()->updateFromCommand($attribute, $command);

        self::assertFalse($attribute->getId()->equals($attributeForUpdate->getId()), "Id mustn't changed");
        self::assertFalse($attribute->getUlid()->equals($attributeForUpdate->getUlid()), "Ulid mustn't changed");
        self::assertTrue($attribute->getCode()->equals($attributeForUpdate->getCode()));
        self::assertTrue($attribute->getType()->equals($attributeForUpdate->getType()));
        self::assertTrue($attribute->getTranslations()->equals($attributeForUpdate->getTranslations()));
        self::assertSame(2, $attribute->getVersion()->value(), "Version mustn't changed");
        self::assertFalse($attribute->getVersion()->equals($attributeForUpdate->getVersion()), "Version mustn't changed");
        self::assertFalse($attribute->getCreatedBy()->equals($attributeForUpdate->getCreatedBy()), "Created by mustn't changed");
        self::assertCount(0, $attribute->getOptions());
        self::assertTrue($attribute->getOptions()->equals($attributeForUpdate->getOptions()));
        self::assertTrue($attribute->getUpdatedBy()->equals($attributeForUpdate->getUpdatedBy()));
    }

    private function createFactory(): AttributeApplicationFactory
    {
        return new AttributeApplicationFactory(ulidGenerator: $this->ulidGenerator);
    }
}
