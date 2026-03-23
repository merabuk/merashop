<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Command\CreateAttribute;

use App\Catalog\Application\Command\CreateAttribute\CreateAttributeCommand;
use App\Catalog\Application\Command\CreateAttribute\CreateAttributeHandler;
use App\Catalog\Application\Service\Attribute\AttributeApplicationFactoryInterface;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Exception\Attribute\AttributeAlreadyExistsException;
use App\Catalog\Domain\Repository\AttributeWriteRepositoryInterface;
use App\Catalog\Domain\Service\Attribute\AttributeValidatorInterface;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Tests\Catalog\Support\AttributeMother;
use App\Tests\Catalog\Support\Traits\AttributeHelperTrait;
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

    public function testItHandleSuccess(): void
    {
        $fakeId = 123;
        $attribute = AttributeMother::createWithData(id: $fakeId);

        $command = $this->fillAndGetCreateCommand($attribute);

        $this->givenCodeIsAvailable($attribute->getCode());
        $this->expectFactoryCreateAttribute($command, $attribute);
        $this->expectSaveAttribute($attribute);

        $resultId = $this->createHandler()($command);

        self::assertSame($fakeId, $resultId);
    }

    public function testThrowsExceptionIfAttributeExists(): void
    {
        $attribute = AttributeMother::createWithData(id: 123);
        $command = $this->fillAndGetCreateCommand($attribute);

        $this->givenCodeIsTaken($command->code);
        $this->factoryCreateAttributeNeverCalled();
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

    private function givenCodeIsTaken(string $code): void
    {
        $this->attributeValidator->expects(self::once())
            ->method('validateCreation')
            ->with(self::callback(fn (Code $c) => $c->value() === $code))
            ->willThrowException(new AttributeAlreadyExistsException());
    }

    private function expectFactoryCreateAttribute(CreateAttributeCommand $command, Attribute $attribute): void
    {
        $this->attributeFactory->expects(self::once())
            ->method('createFromCommand')
            ->with(self::equalTo($command))
            ->willReturn($attribute);
    }

    private function factoryCreateAttributeNeverCalled(): void
    {
        $this->attributeFactory->expects(self::never())->method('createFromCommand');
    }

    private function expectSaveAttribute(Attribute $attribute): void
    {
        $this->writeRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (Attribute $updatedAttribute) use ($attribute): bool {
                $ulidCorrect = $attribute->getUlid()->equals($updatedAttribute->getUlid());
                $codeCorrect = $attribute->getCode()->equals($updatedAttribute->getCode());
                $typeCorrect = $attribute->getType()->equals($updatedAttribute->getType());
                $translationsCorrect = $attribute->getTranslations()->equals($updatedAttribute->getTranslations());
                $createdByCorrect = $attribute->getCreatedBy()->equals($updatedAttribute->getCreatedBy());

                return $ulidCorrect && $codeCorrect && $typeCorrect && $translationsCorrect && $createdByCorrect;
            }))
            ->willReturn($attribute);
    }

    private function saveAttributeNeverCalled(): void
    {
        $this->writeRepository->expects(self::never())->method('save');
    }
}
