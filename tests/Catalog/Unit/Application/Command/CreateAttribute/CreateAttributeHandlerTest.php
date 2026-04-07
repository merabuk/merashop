<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Command\CreateAttribute;

use App\Catalog\Application\Command\CreateAttribute\CreateAttributeCommand;
use App\Catalog\Application\Command\CreateAttribute\CreateAttributeHandler;
use App\Catalog\Application\Service\Attribute\AttributeApplicationFactoryInterface;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\Attribute\AttributeAlreadyExistsException;
use App\Catalog\Domain\Repository\AttributeWriteRepositoryInterface;
use App\Catalog\Domain\Service\Attribute\AttributeValidatorInterface;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Tests\Catalog\Support\AttributeMother;
use App\Tests\Catalog\Support\Traits\AttributeHelperTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreateAttributeHandlerTest extends TestCase
{
    use AttributeHelperTrait;

    private AttributeValidatorInterface&MockObject $attributeValidator;
    private AttributeApplicationFactoryInterface&MockObject $attributeFactory;
    private AttributeWriteRepositoryInterface&MockObject $writeRepository;

    protected function setUp(): void
    {
        $this->attributeValidator = $this->createMock(AttributeValidatorInterface::class);
        $this->attributeFactory = $this->createMock(AttributeApplicationFactoryInterface::class);
        $this->writeRepository = $this->createMock(AttributeWriteRepositoryInterface::class);
    }

    #[DataProvider('attributeDataProvider')]
    public function testItHandleSuccess(
        Attribute $attribute,
        int $expectedId,
    ): void {
        $command = $this->fillAndGetCreateCommand($attribute);

        $this->expectFactoryCreateAttribute($command, $attribute);
        $this->givenCodeIsAvailable($attribute->getCode());
        $this->expectSaveAttribute($attribute);

        $resultId = $this->createHandler()($command);

        self::assertSame($expectedId, $resultId);
    }

    public static function attributeDataProvider(): iterable
    {
        yield 'without options' => [
            'attribute' => AttributeMother::createWithData(type: TypeEnum::String, id: 123),
            'expectedId' => 123,
        ];
        yield 'with options' => [
            'attribute' => AttributeMother::createWithData(type: TypeEnum::Select, id: 123),
            'expectedId' => 123,
        ];
    }

    public function testThrowsExceptionIfAttributeExists(): void
    {
        $attribute = AttributeMother::createWithData(id: 123);
        $command = $this->fillAndGetCreateCommand($attribute);

        $this->expectFactoryCreateAttribute($command, $attribute);
        $this->givenCodeIsTaken($attribute->getCode());
        $this->saveAttributeNeverCalled();

        $this->expectException(AttributeAlreadyExistsException::class);

        $this->createHandler()($command);
    }

    private function createHandler(): CreateAttributeHandler
    {
        return new CreateAttributeHandler(
            attributeValidator: $this->attributeValidator,
            attributeFactory: $this->attributeFactory,
            writeRepository: $this->writeRepository
        );
    }

    private function givenCodeIsAvailable(Code $code): void
    {
        $this->attributeValidator->expects(self::once())
            ->method('validateCreation')
            ->with(self::equalTo($code));
    }

    private function givenCodeIsTaken(Code $code): void
    {
        $this->attributeValidator->expects(self::once())
            ->method('validateCreation')
            ->with(self::equalTo($code))
            ->willThrowException(new AttributeAlreadyExistsException());
    }

    private function expectFactoryCreateAttribute(CreateAttributeCommand $command, Attribute $attribute): void
    {
        $this->attributeFactory->expects(self::once())
            ->method('createFromCommand')
            ->with(self::equalTo($command))
            ->willReturn($attribute);
    }

    private function expectSaveAttribute(Attribute $attribute): void
    {
        $this->writeRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (Attribute $updatedAttribute) use ($attribute): bool {
                self::assertTrue($attribute->getUlid()->equals($updatedAttribute->getUlid()));
                self::assertTrue($attribute->getCode()->equals($updatedAttribute->getCode()));
                self::assertTrue($attribute->getType()->equals($updatedAttribute->getType()));
                self::assertTrue($attribute->getTranslations()->equals($updatedAttribute->getTranslations()));
                self::assertTrue($attribute->getCreatedBy()->equals($updatedAttribute->getCreatedBy()));
                self::assertTrue($attribute->getOptions()->equals($updatedAttribute->getOptions()));

                return true;
            }))
            ->willReturn($attribute);
    }

    private function saveAttributeNeverCalled(): void
    {
        $this->writeRepository->expects(self::never())->method('save');
    }
}
