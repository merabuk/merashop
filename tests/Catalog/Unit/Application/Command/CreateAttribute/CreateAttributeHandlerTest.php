<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Command\CreateAttribute;

use App\Catalog\Application\Command\CreateAttribute\CreateAttributeCommand;
use App\Catalog\Application\Command\CreateAttribute\CreateAttributeHandler;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Exception\Attribute\AttributeAlreadyExistsException;
use App\Catalog\Domain\Repository\AttributeWriteRepositoryInterface;
use App\Catalog\Domain\Service\Attribute\AttributeValidatorInterface;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Tests\Catalog\Support\AttributeMother;
use App\Tests\Shared\Support\Traits\UlidGenerationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreateAttributeHandlerTest extends TestCase
{
    use UlidGenerationTrait;

    private AttributeValidatorInterface&MockObject $attributeValidator;
    private AttributeWriteRepositoryInterface&MockObject $writeRepository;

    protected function setUp(): void
    {
        $this->setUlidGenerator();
        $this->attributeValidator = $this->createMock(AttributeValidatorInterface::class);
        $this->writeRepository = $this->createMock(AttributeWriteRepositoryInterface::class);
    }

    public function testItHandleSuccess(): void
    {
        $fakeId = 123;
        $attribute = AttributeMother::createWithData(id: $fakeId);

        $command = new CreateAttributeCommand(
            code: $attribute->getCode()->value(),
            type: $attribute->getType()->value()->value,
            translations: $attribute->getTranslations()->toArray(),
            adminUlid: $attribute->getCreatedBy()->value()
        );

        $this->givenCodeIsAvailable($attribute->getCode());
        $this->expectGenerateUlid($attribute->getUlid()->value());
        $this->expectSaveAttribute($attribute);

        $resultId = $this->createHandler()($command);

        self::assertSame($fakeId, $resultId);
    }

    public function testThrowsExceptionIfAttributeExists(): void
    {
        $command = new CreateAttributeCommand(
            code: 'duplicate',
            type: 'string',
            translations: ['en' => ['name' => 'Name']],
            adminUlid: '01KHVRCA679BJ6PBXX5N3G6RR5'
        );

        $this->givenCodeIsTaken($command->code);
        $this->generateUlidNeverCalled();
        $this->saveAttributeNeverCalled();

        $this->expectException(AttributeAlreadyExistsException::class);

        $this->createHandler()($command);
    }

    private function createHandler(): CreateAttributeHandler
    {
        return new CreateAttributeHandler(
            attributeValidator: $this->attributeValidator,
            ulidGenerator: $this->ulidGenerator,
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
