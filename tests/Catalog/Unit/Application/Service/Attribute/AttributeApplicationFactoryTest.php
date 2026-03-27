<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Service\Attribute;

use App\Catalog\Application\DTO\Attribute\AttributeOptionData;
use App\Catalog\Application\Service\Attribute\AttributeApplicationFactory;
use App\Catalog\Application\Service\Attribute\AttributeOption\AttributeOptionMetadataProviderInterface;
use App\Catalog\Domain\Entity\AttributeOption;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\AttributeOption\UnsupportedAttributeOptionMetadataTypeException;
use App\Catalog\Domain\ValueObject\AttributeOption\Metadata\AttributeOptionMetadataInterface;
use App\Catalog\Domain\ValueObject\AttributeOption\Ulid as AttributeOptionUlid;
use App\Tests\Catalog\Support\AttributeMother;
use App\Tests\Catalog\Support\AttributeOptionMother;
use App\Tests\Catalog\Support\Traits\AttributeHelperTrait;
use App\Tests\Shared\Support\Traits\UlidGenerationTrait;
use App\Tests\Shared\Support\Traits\ValueObjectAssertionTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

final class AttributeApplicationFactoryTest extends TestCase
{
    use AttributeHelperTrait;
    use UlidGenerationTrait;
    use ValueObjectAssertionTrait;

    private ContainerInterface&MockObject $container;

    protected function setUp(): void
    {
        $this->setUlidGenerator();
        $this->container = $this->createMock(ContainerInterface::class);
    }

    #[DataProvider('attributeOptionUlidsDataProvider')]
    public function testItMapsAttributeOptionUlids(
        array $ulids,
        bool $associative,
        array $expectedUlids,
    ): void {
        $data = array_map(static fn (?string $ulid) => new AttributeOptionData(
            ulid: $ulid,
            code: 'code',
            translations: [],
            isActive: true,
        ), $ulids);

        $result = $this->createFactory()->mapAttributeOptionUlids(optionsData: $data, associative: $associative);

        self::assertCount(count($expectedUlids), $result);
        foreach ($result as $key => $ulid) {
            self::assertInstanceOf(AttributeOptionUlid::class, $ulid);
            self::assertSame($expectedUlids[$key], $ulid->value());
        }
    }

    public static function attributeOptionUlidsDataProvider(): iterable
    {
        $ulids = [
            '01KMDEC4Z9NSK4YPEW8NG5068T',
            '01KMGY62KTY8BJ9J8NHMXHKF4P',
            '01KMJ1ANFES9VS1HYEYBDCNCFW',
        ];

        yield 'simple' => [
            'ulids' => $ulids,
            'associative' => false,
            'expectedUlids' => $ulids,
        ];
        yield 'simple with sparse values' => [
            'ulids' => [
                $ulids[0],
                null,
                $ulids[1],
                null,
                $ulids[2],
            ],
            'associative' => false,
            'expectedUlids' => [
                0 => $ulids[0],
                2 => $ulids[1],
                4 => $ulids[2],
            ],
        ];
        yield 'associative' => [
            'ulids' => $ulids,
            'associative' => true,
            'expectedUlids' => array_combine($ulids, $ulids),
        ];
        yield 'associative with sparse values' => [
            'ulids' => [$ulids[0], null, $ulids[1], null, $ulids[2]],
            'associative' => true,
            'expectedUlids' => array_combine($ulids, $ulids),
        ];
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
        self::assertSame(1, $result->getVersion()->value());
        self::assertTrue($attribute->getCreatedBy()->equals($result->getCreatedBy()));
        self::assertCount(0, $result->getOptions());
        self::assertTrue($attribute->getOptions()->equals($result->getOptions()));
        self::assertNull($result->getUpdatedBy());
    }

    public function testItCreatesFromCommandWithOptions(): void
    {
        $attribute = AttributeMother::createWithData(type: TypeEnum::MultiSelect);
        $command = $this->fillAndGetCreateCommand($attribute);

        $ulids[] = $attribute->getUlid()->value();

        foreach ($attribute->getOptions() as $option) {
            $ulids[] = $option->getUlid()->value();
        }

        $this->expectGenerateManyUlids($ulids);

        $result = $this->createFactory()->createFromCommand($command);

        self::assertTrue($attribute->getUlid()->equals($result->getUlid()));
        self::assertTrue($attribute->getCode()->equals($result->getCode()));
        self::assertTrue($attribute->getType()->equals($result->getType()));
        self::assertTrue($attribute->getTranslations()->equals($result->getTranslations()));
        self::assertSame(1, $result->getVersion()->value());
        self::assertTrue($attribute->getCreatedBy()->equals($result->getCreatedBy()));
        self::assertTrue($attribute->getOptions()->count() > 0);
        self::assertTrue($attribute->getOptions()->equals($result->getOptions()));
        self::assertNull($result->getUpdatedBy());
    }

    public function testItCreatesFromCommandWithOptionsAndMetadata(): void
    {
        $attributeType = TypeEnum::Dimension;
        $attributeOption = AttributeOptionMother::createWithData(attributeType: $attributeType);
        $attribute = AttributeMother::createWithData(type: $attributeType, options: [$attributeOption]);
        $command = $this->fillAndGetCreateCommand($attribute);

        $this->expectGenerateManyUlids([
            $attribute->getUlid()->value(),
            $attributeOption->getUlid()->value(),
        ]);

        $this->expectContainerHasProvider($attributeType);
        $this->expectContainerReturnValidProvider($attributeType, $command->options[0], $attributeOption->getMetadata());

        $result = $this->createFactory()->createFromCommand($command);

        self::assertTrue($attribute->getUlid()->equals($result->getUlid()));
        self::assertTrue($attribute->getCode()->equals($result->getCode()));
        self::assertTrue($attribute->getType()->equals($result->getType()));
        self::assertTrue($attribute->getTranslations()->equals($result->getTranslations()));
        self::assertSame(1, $result->getVersion()->value());
        self::assertTrue($attribute->getCreatedBy()->equals($result->getCreatedBy()));
        self::assertTrue($attribute->getOptions()->count() > 0);
        self::assertTrue($attribute->getOptions()->equals($result->getOptions()));
        self::assertNull($result->getUpdatedBy());
    }

    public function testThrowsExceptionWhenContainerDoesNotHaveMetadataProvider(): void
    {
        $attributeType = TypeEnum::Dimension;
        $attributeOption = AttributeOptionMother::createWithData(attributeType: $attributeType);
        $attribute = AttributeMother::createWithData(type: $attributeType, options: [$attributeOption]);
        $command = $this->fillAndGetCreateCommand($attribute);

        $this->expectGenerateManyUlids([
            $attribute->getUlid()->value(),
            $attributeOption->getUlid()->value(),
        ]);

        $this->expectContainerDoesNotHaveProvider($attributeType);
        $this->expectContainerGetProviderNeverCall();

        $this->expectException(UnsupportedAttributeOptionMetadataTypeException::class);

        $this->createFactory()->createFromCommand($command);
    }

    public function testThrowsExceptionWhenContainerReturnInvalidMetadataProvider(): void
    {
        $attributeType = TypeEnum::Dimension;
        $attributeOption = AttributeOptionMother::createWithData(attributeType: $attributeType);
        $attribute = AttributeMother::createWithData(type: $attributeType, options: [$attributeOption]);
        $command = $this->fillAndGetCreateCommand($attribute);

        $this->expectGenerateManyUlids([
            $attribute->getUlid()->value(),
            $attributeOption->getUlid()->value(),
        ]);

        $this->expectContainerHasProvider($attributeType);
        $this->expectContainerReturnInvalidProvider($attributeType);

        $this->expectException(UnsupportedAttributeOptionMetadataTypeException::class);

        $this->createFactory()->createFromCommand($command);
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
        $command = $this->fillAndGetUpdateCommand(attribute: $attributeForUpdate);

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

    public function testItUpdatesFromCommandWithOptions(): void
    {
        $type = TypeEnum::MultiSelect;
        $existingOption1 = AttributeOptionMother::createWithData(
            ulid: '01KMDEC4Z9NSK4YPEW8NG5068T',
            code: 'op-code-1',
        );
        $existingOption2 = AttributeOptionMother::createWithData(
            ulid: '01KMGY62KTY8BJ9J8NHMXHKF4P',
            code: 'op-code-2',
        );
        $willBeDeactivated = AttributeOptionMother::createWithData(
            ulid: '01KMJ1ANFES9VS1HYEYBDCNCFW',
            code: 'op-code-3',
        );
        $willBeAdded = AttributeOptionMother::createWithData(
            ulid: '01KMNT82QHCRRFAQA0RMTKK1H2',
            code: 'op-code-4',
        );

        $attribute = AttributeMother::createWithData(
            ulid: AttributeMother::DEFAULT_ULID,
            code: 'old-code',
            type: $type,
            translations: [
                'en' => ['name' => 'Old Attribute'],
            ],
            version: 2,
            createdByUlid: AttributeMother::DEFAULT_ADMIN_ULID,
            options: [$existingOption1, $existingOption2, $willBeDeactivated],
            id: 123
        );
        $attributeForUpdate = AttributeMother::createWithData(
            ulid: '01KME5MMDTM1607C5T3X8YHDB0',
            type: $type,
            version: 1,
            createdByUlid: '01KM2VXDT53FFTM8A520WFDK9T',
            options: [$existingOption1, $existingOption2, $willBeAdded],
            updatedByUlid: AttributeMother::DEFAULT_ADMIN_ULID,
            id: 456
        );

        $command = $this->fillAndGetUpdateCommand(
            attribute: $attributeForUpdate,
            newOptionUlids: [$willBeAdded->getUlid()->value()],
        );

        $this->expectGenerateUlid($willBeAdded->getUlid()->value());

        $this->createFactory()->updateFromCommand($attribute, $command);

        self::assertFalse($attribute->getId()->equals($attributeForUpdate->getId()), "Id mustn't changed");
        self::assertFalse($attribute->getUlid()->equals($attributeForUpdate->getUlid()), "Ulid mustn't changed");
        self::assertTrue($attribute->getCode()->equals($attributeForUpdate->getCode()));
        self::assertTrue($attribute->getType()->equals($attributeForUpdate->getType()));
        self::assertTrue($attribute->getTranslations()->equals($attributeForUpdate->getTranslations()));
        self::assertSame(2, $attribute->getVersion()->value(), "Version mustn't changed");
        self::assertFalse($attribute->getVersion()->equals($attributeForUpdate->getVersion()), "Version mustn't changed");
        self::assertFalse($attribute->getCreatedBy()->equals($attributeForUpdate->getCreatedBy()), "Created by mustn't changed");
        self::assertCount(4, $attribute->getOptions());
        foreach ([$existingOption1, $existingOption2, $willBeDeactivated, $willBeAdded] as $option) {
            /** @var AttributeOption $option */
            $actual = $attribute->getOptions()->getByUlid($option->getUlid());
            self::assertNotNull($actual);
            self::assertTrue($option->getUlid()->equals($actual->getUlid()));
            self::assertTrue($option->getCode()->equals($actual->getCode()));
            self::assertTrue($option->getTranslations()->equals($actual->getTranslations()));
            if ($option->getUlid()->equals($willBeDeactivated->getUlid())) {
                self::assertFalse($actual->isActive()->value());
            } else {
                self::assertTrue($option->isActive()->equals($actual->isActive()));
            }
            self::assertTrue($option->getVersion()->equals($actual->getVersion()));
            self::assertTrue($option->getCreatedBy()->equals($actual->getCreatedBy()));
            $this->assertVoEqualsOrNull($option->getMetadata(), $actual->getMetadata());
            $this->assertVoEqualsOrNull($option->getUpdatedBy(), $actual->getUpdatedBy());
        }
        self::assertTrue($attribute->getUpdatedBy()->equals($attributeForUpdate->getUpdatedBy()));
    }

    private function createFactory(): AttributeApplicationFactory
    {
        return new AttributeApplicationFactory(
            ulidGenerator: $this->ulidGenerator,
            providers: $this->container,
        );
    }

    private function expectContainerHasProvider(TypeEnum $type): void
    {
        $this->expectContainerCheck($type, true);
    }

    private function expectContainerDoesNotHaveProvider(TypeEnum $type): void
    {
        $this->expectContainerCheck($type, false);
    }

    private function expectContainerCheck(TypeEnum $type, bool $expectedResult): void
    {
        $this->container->expects(self::once())
            ->method('has')
            ->with(self::equalTo($type->value))
            ->willReturn($expectedResult);
    }

    private function expectContainerReturnValidProvider(
        TypeEnum $type,
        AttributeOptionData $data,
        AttributeOptionMetadataInterface $metadata,
    ): void {
        $provider = $this->createMock(AttributeOptionMetadataProviderInterface::class);
        $provider->expects(self::once())
            ->method('handle')
            ->with(self::equalTo($data))
            ->willReturn($metadata);

        $this->expectContainerReturnProviderCheck($type, $provider);
    }

    private function expectContainerReturnInvalidProvider(TypeEnum $type): void
    {
        $this->expectContainerReturnProviderCheck($type, new stdClass());
    }

    private function expectContainerReturnProviderCheck(TypeEnum $type, object $provider): void
    {
        $this->container->expects(self::once())
            ->method('get')
            ->with(self::equalTo($type->value))
            ->willReturn($provider);
    }

    private function expectContainerGetProviderNeverCall(): void
    {
        $this->container->expects(self::never())->method('get');
    }
}
